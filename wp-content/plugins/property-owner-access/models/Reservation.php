<?php

use Corcel\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Capsule\Manager as DB;

class Reservation extends Model
{
    protected $table = 'vikbooking_orders';
    protected static $channels;
    protected static $allCommissions;

    // Disable timestamps
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope('condition', function (Builder $builder) {
            $builder->whereRaw('IFNULL(total, 0) - IFNULL(tot_fees, 0) !=  0');
        });
        // preload channels
        static::$channels = Channel::all();
        // preload every commission row once
        static::$allCommissions = PropertyOwnerCommission::query()
            ->join('poa_property_owners','poa_property_owners.id','=','poa_property_owners_commissions.property_owner_id')
            ->get([
            'poa_property_owners.room_id as room_id',
            'poa_property_owners_commissions.*'
            ])
            ->groupBy('room_id');
    }

    // Relatie cu tabelul vikbooking_ordersrooms
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'vikbooking_ordersrooms', 'idorder', 'idroom')
            ->using(ReservationProperty::class)
            ->withPivot([
                'room_cost',
                'cust_cost',
            ]);
    }
    
    public function channels() {
        return static::$channels;
    }

    // eager-loaded relation for channel fees
    public function channelFees()
    {
        return $this->hasManyThrough(
            ChannelFee::class,
            ReservationProperty::class,
            'idorder',       // ReservationProperty.idorder → Reservation.id
            'OtaFeeTbl_apt', // ChannelFee.OtaFeeTbl_apt → ReservationProperty.idroom
            'id',            // Reservation.id
            'idroom'         // ReservationProperty.idroom
        )
        ->where('OtaFeeTbl_date',  '<=', date('Y-m-d', $this->checkout))
        ->where('OtaFeebl_DateTo', '>=', date('Y-m-d', $this->checkout));
    }

    // eager-loaded relation for owner commissions
    public function ownerCommissions()
    {
        return $this->hasManyThrough(
            PropertyOwnerCommission::class,
            PropertyOwner::class,
            'room_id',            // ReservationProperty.idroom -> PropertyOwner.room_id
            'property_owner_id',  // Commission.property_owner_id
            'id',                 // Reservation.id -> PropertyOwner.id
            'id'                  // PropertyOwner.id -> Commission.property_owner_id
        );
    }

    public function getTotal($propertyId=null)
    {
        $total = 0;
        $properties = $this->properties;
        if($propertyId) {
            $properties = $properties->where('id', $propertyId);
        }
        $totalRoomCost = $properties->sum(function($property) {
            return $property->pivot->room_cost ?? 0;
        });
        $totalCustCost = $properties->sum(function($property) {
            return $property->pivot->cust_cost ?? 0;
        });
        // if total room cost is not empty, return it, else return total customer cost
        if($totalRoomCost) {
            $total = $totalRoomCost;
        } else {
            $total = $totalCustCost;
        }
        // if coupon is not empty, apply discount
        if( !empty($this->coupon) ) {
            $coupon = explode(';', $this->coupon);
            if(count($coupon) >= 2) {
                $total -= $coupon[1] * $this->getPropertyRatioFromReservationTotal($propertyId);
            }
        }
        // if channel is not empty, apply extra fees
        if( !empty($this->channel) ) {
            $total -= $this->getChannelExtraFees();
        }
        return $total;
    }

    public function getTransferCommission($propertyId=null)
    {
        // Extract numeric ID from idpayment if in format "3=Cartão de Crédito"
		$paymentParts = explode('=', $this->idpayment);
		$paymentId = intval($paymentParts[0]);
        // if id payment is 2, 3 or 4, return 4% of total
        if ( $this->channel != 'airbnbapi_Airbnb' && in_array($paymentId, [2, 3, 4]) ) {
            $commission = $this->getTotal($propertyId) * 0.04;
            return $commission;
        }
        return 0;
    }

    public function getUnhotelCommission($propertyId=null)
    {
        $propertiesIds = $this->properties->pluck('id');
        $date = date('Y-m-d', $this->checkout);
        $commissionPercentage = 0;
        // look up in the preloaded static map
        foreach ($propertiesIds as $roomId) {
            $comms = static::$allCommissions[$roomId] ?? collect();
            $match = $comms->first(function($c) use ($date) {
                return $c->date_from <= $date && $c->date_to >= $date;
            });
            if ($match) {
                $commissionPercentage = $match->percentage;
                break;
            }
        }
        $totalNet = $this->getTotal($propertyId) - $this->getCmms($propertyId) - $this->getTransferCommission($propertyId);
        return round(($commissionPercentage * $totalNet) / 100, 2);
    }

    public function getTotalCommission($propertyId=null)
    {
        return $this->getTransferCommission($propertyId) + $this->getUnhotelCommission($propertyId);
    }

    public function getTotalNet($propertyId=null)
    {
        return $this->getTotal($propertyId) - $this->getCmms($propertyId) - $this->getTotalCommission($propertyId);
    }

    public function getChannel() {
        $result = ['id' => null, 'name' => '', 'image' => ''];
        // Get default channel
        $channel = $this->channels()->where('OTA_name', 'unhotel')->first();
        if( !empty($channel) ) {
            $result['id'] = $channel->id;
            $result['name'] = $channel->OTA_name;
            $result['image'] = '<img src="'.$channel->ota_logo_file.'" alt="'.( $channel->OTA_name ?? '' ).'" class="channel-logo">';
        }
        if( empty($this->channel) ) {
            return $result;
        }
        // Select all channels from database and compare with reservation channel
        foreach ($this->channels() as $channel) {
            if (stripos($this->channel, $channel->OTA_name) !== false) {
                if( !empty($channel->ota_logo_file) ) {
                    $result['image'] = '<img src="'.$channel->ota_logo_file.'" alt="'.( $channel->OTA_name ?? '' ).'" class="channel-logo">';
                }
                if( !empty($channel->OTA_name) ) {
                    $result['name'] = $channel->OTA_name;
                }
                $result['id'] = $channel->id;
                return $result;
            }
        }
        return $result;
    }

    public function getChannelExtraFees() {
        $channelId = $this->getChannel()['id'];
        // Sum only this channel’s fees (the relation itself is already date-filtered)
        $channelFee = $this->channelFees
            ->where('OtaFeeTbl_OtaCode', $channelId)
            ->sum('OtaFeeTbl_amount');
        return $channelFee ?: 0;
    }

    public function getCmms($propertyId=null)
    {
        return $this->cmms * $this->getPropertyRatioFromReservationTotal($propertyId);
    }

    private function getPropertyRatioFromReservationTotal($propertyId=null) {
        $properties = $this->properties;
        $reservationPropertiesCount = $properties->count();
        // Use pivot to sum costs
        $reservationTotal = ( ( !empty($properties->sum(fn($property) => $property->pivot->room_cost ?? 0)) )
            ? $properties->sum(fn($property) => $property->pivot->room_cost ?? 0)
            : $properties->sum(fn($property) => $property->pivot->cust_cost ?? 0)
        );
        if ($propertyId) {
            $properties = $properties->where('id', $propertyId);
        }
        $totalRoomCost = $properties->sum(fn($property) => $property->pivot->room_cost ?? 0);
        $totalCustCost = $properties->sum(fn($property) => $property->pivot->cust_cost ?? 0);

        $total = $totalRoomCost ?: $totalCustCost;

        $currentPropertyRatio = 1;
        if (empty($propertyId) || $reservationPropertiesCount == 1) {
            return $currentPropertyRatio;
        }
        if (!empty($reservationTotal)) {
            $currentPropertyRatio = $total / $reservationTotal;
        }
        return $currentPropertyRatio;
    }
}