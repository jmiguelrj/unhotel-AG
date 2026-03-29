import React, { useState, useEffect, useMemo } from 'react';
import './index.css';
import './unhotel-dashboard-style.css';
import en_US from './locales/en_US';
import pt_BR from './locales/pt_BR';

// --- i18n Helper ---
const DICTIONARIES = { en_US, pt_BR };
const CURRENT_LANG = window.unhotelData?.language || 'en_US';
const t = (key) => {
  const dict = DICTIONARIES[CURRENT_LANG] || DICTIONARIES['en_US'];
  return dict[key] || DICTIONARIES['en_US'][key] || key;
};
import { Icons, SafeIcon } from './utils/icons';
const {
  Search, ChevronRight, User, MessageSquare, CheckCircle2, FileText,
  MoreHorizontal, Phone, Mail, LogIn, LogOut, AlertCircle, Check,
  Copy, RefreshCw, ArrowUp, ArrowDown, X, Zap,
  Plane, Car, Bus, Footprints, MapPin, Clock,
  Users, Moon, Star, Menu
} = Icons;

// --- API Helpers ---
const API_ROOT = window.unhotelData?.root || '/wp-json/';
const API_NONCE = window.unhotelData?.nonce || '';
const USER_NAME = window.unhotelData?.userName || 'Receptionist';
const STATUS_SETTINGS = window.unhotelData?.statusSettings || [
  { slug: 'registration', label: 'Registration', color: 'amber' },
  { slug: 'cleaning', label: 'Cleaning', color: 'blue' },
  { slug: 'checkin', label: 'Check-in', color: 'emerald' },
  { slug: 'instructed', label: 'Instructed', color: 'purple' },
  { slug: 'contacted', label: 'Contacted', color: 'sky' }
];

const apiFetch = async (path, options = {}) => {
  const url = `${API_ROOT}${path}`;
  const headers = {
    'Content-Type': 'application/json',
    'X-WP-Nonce': API_NONCE,
    ...options.headers
  };
  console.log(`[API] Fetching: ${url}`);

  // Timeout Logic (60s) to prevent premature AbortError
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), 60000);

  try {
    const response = await fetch(url, { ...options, headers, signal: controller.signal });
    clearTimeout(id);

    if (!response.ok) {
      throw new Error(`API Error: ${response.statusText} (${response.status})`);
    }
    const json = await response.json();
    console.log(`[API] Response for ${path}:`, json);
    return json;
  } catch (error) {
    clearTimeout(id);
    console.error(`[API] Failed:`, error);
    throw error;
  }
};

const formatCurrency = (val) => {
  if (isNaN(val)) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
};

// Helper: Auto-Format Time (1155 -> 11:55)
const autoFormatTime = (input) => {
  if (!input) return '';
  // Remove colons to check raw numbers
  const raw = input.replace(/:/g, '');

  if (raw.length < 3 || raw.length > 4) return input; // Return original if not 3-4 digits (e.g. 900 -> 9:00, 1155 -> 11:55) or already valid

  let hours, minutes;
  if (raw.length === 3) {
    hours = raw.substring(0, 1);
    minutes = raw.substring(1);
  } else {
    hours = raw.substring(0, 2);
    minutes = raw.substring(2);
  }

  // Basic Validation
  const h = parseInt(hours, 10);
  const m = parseInt(minutes, 10);

  if (h >= 0 && h < 24 && m >= 0 && m < 60) {
    return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
  }
  return input;
};

// --- Error Boundary ---
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }
  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }
  componentDidCatch(error, errorInfo) {
    console.error("Uncaught error:", error, errorInfo);
  }
  render() {
    if (this.state.hasError) {
      return (
        <div className="p-10 text-center">
          <h2 className="text-xl font-bold text-red-600 mb-2">{t("Something went wrong.")}</h2>
          <details className="text-left bg-gray-100 p-4 rounded text-xs text-red-800 whitespace-pre-wrap">
            {this.state.error && this.state.error.toString()}
          </details>
        </div>
      );
    }
    return this.props.children;
  }
}

// --- Components ---
// ... (Previous components kept same, simplified in this view)
const InlineTimeEdit = ({ time, onSave }) => {
  const [isEditing, setIsEditing] = useState(false);
  const [val, setVal] = useState(time || '');
  const [saved, setSaved] = useState(false);

  useEffect(() => { setVal(time || ''); }, [time]);

  const handleBlur = () => {
    setIsEditing(false);
    if (val !== time) {
      setVal(autoFormatTime(val));
      onSave(autoFormatTime(val));
      setSaved(true);
      setTimeout(() => setSaved(false), 2000);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') handleBlur();
  };

  if (isEditing) {
    return (
      <input
        type="text" autoFocus value={val}
        onChange={(e) => {
          // Allow only numbers and colon
          const v = e.target.value.replace(/[^0-9:]/g, '');
          setVal(v);
        }}
        onBlur={() => {
          const formatted = autoFormatTime(val);
          setVal(formatted);
          handleBlur(formatted);
        }}
        onKeyDown={handleKeyDown}
        className="w-16 px-1 py-0.5 text-sm border border-[#FF4F7C] rounded focus:outline-none focus:ring-1 focus:ring-[#FF4F7C] text-center"
        placeholder="HH:mm"
        maxLength={5}
      />
    );
  }

  return (
    <div className="group/time flex items-center gap-1 cursor-pointer hover:bg-gray-100 px-1 py-0.5 rounded relative" onClick={() => setIsEditing(true)}>
      <span className={`text-sm font-bold ${!val ? 'text-gray-300' : 'text-slate-700'}`}>{val || '--:--'}</span>
      {saved && <span className="absolute -right-4 text-green-500"><SafeIcon icon={Check} size={12} /></span>}
    </div>
  );
};

const GuestAvatar = ({ guest }) => {
  if (guest.pic) {
    return <img src={guest.pic} alt={guest.name} className="w-8 h-8 rounded-full object-cover border border-gray-200" />;
  }
  const nameToUse = guest.fullName || 'Guest';
  const initials = nameToUse.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
  const colors = ['bg-indigo-100 text-indigo-600', 'bg-rose-100 text-rose-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600'];
  const colorClass = colors[nameToUse.length % colors.length];
  return <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold ${colorClass}`}>{initials}</div>;
};

const StatusPill = ({ status, onClick, compact = false, lookups = [] }) => {
  // If lookups provided, find config
  let label = status;
  let color = '#ccc';
  let isHex = false;

  if (lookups.length > 0) {
    // Match by Label (since stored status is slug/text, but DB uses Label as unique key in our plan logic? No, stored is slug 'registration'.
    // Wait, DB seeds: type='registration_status', value='#HEX', label='Pendente' (Priority 0).
    // My API logic: stored status in `wp_jet_cct` is currently "registration" (slug).
    // But with new system, we should store the LABEL or maintain a SLUG?
    // The prompt says: "Map: value -> Background Color, label -> Status Name".
    // It DOES NOT specify a slug. It says "Default State: The status with priority = 0 (Label: 'Pendente')".
    // If we store 'Pendente' in the DB column `status_reception`, that works.
    // Existing data has slugs 'registration', 'checkin'. Use Fallback.
  }

  const globalHex = window.unhotelData?.statusColors?.[status];
  
  // Find matching lookup
  const found = lookups.find(l => l.label === status || l.label.toLowerCase() === status.toLowerCase());
  if (found) {
    label = found.label;
    color = found.value || globalHex || '#ccc';
    isHex = color.startsWith('#');
  } else if (globalHex) {
    label = status;
    color = globalHex;
    isHex = true;
  } else {
    // Fallback to legacy settings
    const setting = STATUS_SETTINGS.find(s => s.slug === status) || STATUS_SETTINGS.find(s => s.slug === 'registration') || { label: status, color: 'gray' };
    label = setting.label;
    color = setting.color;
  }

  const style = isHex ? {
    backgroundColor: color,
    color: '#0f172a', // Slate-900 for high contrast
    borderColor: 'transparent'
  } : {};

  const styleClass = isHex ? '' : `bg-${color}-100 text-${color}-800 border-${color}-200 hover:bg-${color}-200`;

  return (
    <button onClick={(e) => { e && e.stopPropagation(); onClick && onClick(); }}
      style={style}
      className={`${styleClass} flex items-center justify-center font-medium rounded-full transition-colors whitespace-nowrap ${compact ? 'px-3 py-1 text-[10px]' : 'px-4 py-1.5 text-sm w-full'}`}
    >
      {label}
    </button>
  );
};

const RegistrationStatusDropdown = ({ currentStatus, onChange, lookups = [] }) => {
  const [isOpen, setIsOpen] = useState(false);
  const ref = React.useRef(null);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (ref.current && !ref.current.contains(event.target)) setIsOpen(false);
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSelect = (statusLabel) => {
    onChange(statusLabel);
    setIsOpen(false);
  };

  return (
    <div className="relative" ref={ref}>
      <StatusPill status={currentStatus} lookups={lookups} onClick={() => setIsOpen(!isOpen)} />
      {isOpen && (
        <div className="absolute top-full left-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-xl z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-100">
          {lookups.map((l) => (
            <div
              key={l.id || l._ID}
              onClick={() => handleSelect(l.label)}
              className="px-3 py-2 text-sm hover:bg-gray-50 cursor-pointer flex items-center gap-2 border-b border-gray-50 last:border-0"
            >
              <span className="w-2 h-2 rounded-full" style={{ backgroundColor: l.value }}></span>
              <span>{l.label}</span>
              {currentStatus === l.label && <SafeIcon icon={Check} size={12} className="ml-auto text-indigo-600" />}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

const formatDateISO = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Check if booking ts is "Today"
// Check if booking ts occurs on same day as checkin
const isSameDayBooking = (ts, checkinIso) => {
  if (!ts || !checkinIso) return false;
  // Convert ts (seconds) to YYYY-MM-DD
  const bookedDate = new Date(ts * 1000);
  const bookedIso = formatDateISO(bookedDate);
  return bookedIso === checkinIso;
  return bookedIso === checkinIso;
};

// --- Arrival Logistics Components ---


const AutocompleteInput = ({ value, onChange, placeholder, suggestions = [], icon: Icon }) => {
  const [show, setShow] = useState(false);
  const filtered = value && value.length >= 3 ? suggestions.filter(s => s.toLowerCase().includes(value.toLowerCase())) : [];

  return (
    <div className="relative">
      {Icon && <SafeIcon icon={Icon} className="absolute right-3 top-3 text-slate-400 pointer-events-none" size={16} />}
      <input
        type="text"
        className={`w-full border border-slate-300 rounded-lg py-2 focus:ring-2 focus:ring-indigo-500 outline-none ${Icon ? 'pl-3 pr-9' : 'px-3'}`}
        placeholder={placeholder}
        value={value}
        onChange={(e) => { onChange(e.target.value); setShow(true); }}
        onBlur={() => setTimeout(() => setShow(false), 200)}
        onFocus={() => setShow(true)}
      />
      {show && filtered.length > 0 && (
        <div className="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 z-50 max-h-40 overflow-y-auto">
          {filtered.map((s, i) => (
            <div
              key={i}
              className="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm truncate"
              onClick={() => onChange(s)}
            >
              {s}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

const ArrivalLogisticsCard = ({ guest, onEdit }) => {
  const l = guest.logistics || {};
  const hasData = l.transportType || l.flightNumber || l.arrivingFrom;

  return (
    <div className="mb-6">
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
          <SafeIcon icon={Plane} size={14} /> {t("Arrival Details")}
        </h3>
        <button
          onClick={onEdit}
          className="text-[#FF4F7C] hover:text-[#FF4F7C] text-xs font-medium flex items-center gap-1 bg-[#FF4F7C]/10 px-2 py-1 rounded transition-colors"
        >
          {hasData ? <SafeIcon icon={Icons.FileText} size={12} /> : <SafeIcon icon={Icons.ArrowDown} size={12} />}
          {hasData ? t("Edit Details") : t("Add Details")}
        </button>
      </div>

      {!hasData ? (
        <div className="bg-slate-50 border border-slate-100 rounded-lg p-4 text-center">
          <p className="text-slate-400 text-sm">{t("No arrival details added yet.")}</p>
        </div>
      ) : (
        <div className="bg-white border border-slate-200 rounded-lg p-4 shadow-sm relative overflow-hidden">
          {/* Decorative Top Line */}
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#FF4F7C] to-[#FF8FA9]"></div>

          {l.transportType === 'plane' ? (
            <div className="flex items-center justify-between">
              <div>
                <div className="flex items-center gap-2 mb-1">
                  <span className="text-2xl font-bold text-slate-800">{l.flightNumber || '---'}</span>
                  {l.airport && (
                    <span className="bg-[#FF4F7C]/10 text-[#FF4F7C] text-xs font-bold px-1.5 py-0.5 rounded border border-[#FF4F7C]/20">
                      {l.airport}
                    </span>
                  )}
                </div>
                <p className="text-xs text-slate-500 uppercase font-semibold tracking-wide">{t("Flight Number")}</p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold text-slate-800 flex items-center gap-1 justify-end">
                  {l.landingTime || '--:--'}
                  {l.nextDay && <span className="text-red-500 text-xs font-bold align-top">(+1)</span>}
                </div>
                <p className="text-xs text-slate-500 uppercase font-semibold tracking-wide">{t("Est. Landing")}</p>
              </div>
            </div>
          ) : (
            <div>
              <div className="flex items-center gap-2 mb-2">
                {l.transportType === 'car' && <SafeIcon icon={Car} className="text-slate-400" size={20} />}
                {l.transportType === 'bus' && <SafeIcon icon={Bus} className="text-slate-400" size={20} />}
                {l.transportType === 'walk' && <SafeIcon icon={Footprints} className="text-slate-400" size={20} />}
                <span className="text-lg font-bold text-slate-800">
                  {l.arrivingFrom ? `${t("Arriving from")} ${l.arrivingFrom}` : t("Arrival Details")}
                </span>
              </div>
              <div className="flex items-center gap-2 text-sm text-slate-600">
                {l.transportType && (
                  <span className="bg-slate-100 px-2 py-0.5 rounded text-xs font-semibold capitalize">
                    {t("Via")} {l.transportType}
                  </span>
                )}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

const ArrivalLogisticsModal = ({ guest, isOpen, onClose, onSave, lookups, suggestions }) => {
  const [data, setData] = useState({
    transportType: 'plane',
    flightNumber: '',
    airport: '',
    landingTime: '',
    nextDay: false,
    arrivingFrom: ''
  });

  useEffect(() => {
    if (guest && guest.logistics) {
      setData({
        transportType: guest.logistics.transportType || 'plane',
        flightNumber: guest.logistics.flightNumber || '',
        airport: guest.logistics.airport || '',
        landingTime: guest.logistics.landingTime || '',
        nextDay: !!guest.logistics.nextDay,
        arrivingFrom: guest.logistics.arrivingFrom || ''
      });
    }
  }, [guest]);

  if (!isOpen) return null;

  const handleChange = (key, val) => setData(prev => ({ ...prev, [key]: val }));

  const TransportTile = ({ type, icon: Icon, label }) => (
    <div
      onClick={() => handleChange('transportType', type)}
      className={`
        cursor-pointer rounded-xl border p-3 flex flex-col items-center justify-center gap-2 transition-all
        ${data.transportType === type
          ? 'bg-[#FF4F7C]/10 border-[#FF4F7C] text-[#FF4F7C] ring-1 ring-[#FF4F7C]'
          : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300'}
      `}
    >
      <SafeIcon icon={Icon} size={24} />
      <span className="text-xs font-bold">{label}</span>
    </div>
  );

  return (
    <div className="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
        <div className="bg-slate-900 text-white p-4 flex justify-between items-center">
          <h3 className="font-bold flex items-center gap-2">
            <SafeIcon icon={Plane} size={18} /> {t("Arrival Details")}
          </h3>
          <button onClick={onClose} className="text-slate-400 hover:text-white"><SafeIcon icon={X} size={20} /></button>
        </div>

        <div className="p-5 space-y-5">
          {/* Type Selector */}
          <div>
            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">{t("Transformation Type")}</label>
            <div className="grid grid-cols-4 gap-2">
              <TransportTile type="plane" icon={Plane} label={t("Plane")} />
              <TransportTile type="car" icon={Car} label={t("Car")} />
              <TransportTile type="bus" icon={Bus} label={t("Bus")} />
              <TransportTile type="walk" icon={Footprints} label={t("Walk")} />
            </div>
          </div>

          {/* Conditional Inputs */}
          {data.transportType === 'plane' ? (
            <div className="space-y-4 animate-in slide-in-from-right-4 duration-300">
              <div>
                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">{t("Flight Number")}</label>
                <AutocompleteInput
                  value={data.flightNumber}
                  onChange={(val) => handleChange('flightNumber', val.toUpperCase())}
                  placeholder={t("e.g. UA-104")}
                  suggestions={suggestions?.flight_number || []}
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">{t("Airport")}</label>
                  <select
                    className="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#FF4F7C] outline-none"
                    value={data.airport}
                    onChange={(e) => handleChange('airport', e.target.value)}
                  >
                    <option value="">{t("Select...")}</option>
                    {lookups && lookups.airports_list && lookups.airports_list.map(l => (
                      <option key={l.id || l._ID} value={l.label}>{l.label}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">{t("Landing Time")}</label>
                  <input
                    value={data.landingTime}
                    onChange={(e) => {
                      const v = e.target.value.replace(/[^0-9:]/g, '');
                      handleChange('landingTime', v);
                    }}
                    onBlur={(e) => {
                      const formatted = autoFormatTime(e.target.value);
                      handleChange('landingTime', formatted);
                    }}
                    type="text"
                    placeholder={t("HH:MM")}
                  />
                </div>
              </div>

              <div className="flex items-center justify-between bg-slate-50 p-3 rounded-lg border border-slate-100">
                <label className="text-sm font-medium text-slate-700">{t("Lands next day?")}</label>
                <button
                  onClick={() => handleChange('nextDay', !data.nextDay)}
                  className={`w-10 h-6 rounded-full transition-colors relative ${data.nextDay ? 'bg-[#FF4F7C]' : 'bg-slate-300'}`}
                >
                  <div className={`absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform ${data.nextDay ? 'left-5' : 'left-1'}`}></div>
                </button>
              </div>
            </div>
          ) : (
            <div className="animate-in slide-in-from-right-4 duration-300">
              <label className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">{t("Arriving From")}</label>
              <AutocompleteInput
                value={data.arrivingFrom}
                onChange={(val) => handleChange('arrivingFrom', val)}
                placeholder={t("City or Location...")}
                suggestions={suggestions?.arriving_from || []}
                icon={MapPin}
              />
            </div>
          )}

          <button
            onClick={() => onSave(data)}
            className="w-full bg-[#FF4F7C] hover:bg-[#FF4F7C]/90 text-white font-bold py-3 rounded-xl shadow-lg shadow-[#FF4F7C]/20 transition-all active:scale-95"
          >
            {t("Save Details")}
          </button>
        </div>
      </div>
    </div>
  );
};

const LastMinuteWidget = ({ active, onClick, isAlerting, onCountChange }) => {
  const [count, setCount] = useState(0);
  const [alert, setAlert] = useState(false);
  const [loading, setLoading] = useState(true);

  const fetchCount = async () => {
    try {
      const data = await apiFetch('unhotel/v1/last-minute-count');
      const newCount = data.count || 0;
      setCount(newCount);
      setAlert(data.alert || false);
      // Propagate count to parent
      if (onCountChange) onCountChange(newCount);

      setLoading(false);
    } catch (err) {
      console.error("Last Minute Fetch Failed", err);
    }
  };

  useEffect(() => {
    fetchCount();
    const interval = setInterval(fetchCount, 60000); // 60s
    return () => clearInterval(interval);
  }, []); // Run on mount

  const handleWidgetClick = async () => {
    if (onClick) onClick();
    if (alert) {
      setAlert(false); // Optimistically clear
      try {
        await apiFetch('unhotel/v1/last-minute-ack', { method: 'POST' });
      } catch(e) { console.error('Last Minute Ack Failed', e); }
    }
  };

  const trueAlert = alert || isAlerting;

  return (
    <div onClick={handleWidgetClick} className={`bg-white border rounded-xl p-3 shadow-sm cursor-pointer transition-all hover:shadow-md ${active ? 'border-[#FF4F7C] ring-1 ring-[#FF4F7C]' : 'border-gray-200'} ${trueAlert ? 'bg-rose-100 border-rose-400 animate-pulse' : ''}`}>
      <div className="flex justify-between items-start mb-2">
        <div className={`p-1.5 rounded-lg ${active ? 'bg-[#FF4F7C] text-white' : trueAlert ? 'bg-white text-rose-500' : 'bg-gray-100 text-gray-400'}`}>
          <SafeIcon icon={Zap} size={16} fill={active || trueAlert ? "currentColor" : "none"} />
        </div>
        <span className={`text-xs font-bold px-2 py-1 rounded-full ${trueAlert ? 'bg-white text-rose-600' : 'bg-gray-100 text-gray-600'}`}>{count}</span>
      </div>
      <div className={`text-sm font-bold ${trueAlert ? 'text-rose-900' : 'text-slate-700'}`}>{t("Last Minute")}</div>
    </div>
  );
};

function App() {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [currentMonth, setCurrentMonth] = useState(new Date());
  const [activeFilter, setActiveFilter] = useState('all');
  const [selectedGuestId, setSelectedGuestId] = useState(null);
  const [guests, setGuests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false); // Mobile Sidebar State
  const [guestCache, setGuestCache] = useState({});
  const [hasPrefetched, setHasPrefetched] = useState(false);
  const [searchVal, setSearchVal] = useState('');
  const [sortConfig, setSortConfig] = useState({ key: 'time', dir: 'asc' });
  const [noteInput, setNoteInput] = useState('');
  const [copiedId, setCopiedId] = useState(null);

  // Toast State
  const [toastMessage, setToastMessage] = useState(null);

  // Last Minute Alert State
  // Track remote count to compare with local data (Discrepancy Logic)
  const [remoteLMCount, setRemoteLMCount] = useState(0);

  useEffect(() => {
    if (toastMessage) {
      const timer = setTimeout(() => setToastMessage(null), 3000);
      return () => clearTimeout(timer);
    }
  }, [toastMessage]);

  // Logistics
  const [isLogisticsModalOpen, setIsLogisticsModalOpen] = useState(false);
  const [lookups, setLookups] = useState({});
  const [suggestions, setSuggestions] = useState({ flight_number: [], arriving_from: [] });

  // Status Logic
  const statusPriority = { 'Pendente': 0, 'Contato': 1, 'Doc Recebido': 2, 'Cadastrado': 3, 'Instruído': 4, 'Check-in': 5 };
  const [statusSort, setStatusSort] = useState(null);
  const [statusFilter, setStatusFilter] = useState(null);

  // Refs
  const guestListRef = React.useRef(null);

  // Dynamic Filters
  const [filterGroups, setFilterGroups] = useState([]);
  const [expandedGroups, setExpandedGroups] = useState({}); // { prefix: boolean }

  useEffect(() => {
    // Fetch Filter Config
    apiFetch('unhotel/v1/filters').then(data => {
      if (Array.isArray(data)) {
        setFilterGroups(data);
      }
    }).catch(err => console.warn("Filter fetch failed", err));

    // Fetch lookups
    apiFetch('unhotel/v1/lookups').then(data => {
      if (Array.isArray(data)) {
        const grouped = {};
        data.forEach(item => {
          if (!grouped[item.type]) grouped[item.type] = [];
          grouped[item.type].push(item);
        });
        setLookups(grouped);
      }
    }).catch(err => console.warn("Lookups fetch failed", err));

    // Fetch Suggestions
    ['flight_number', 'arriving_from'].forEach(field => {
      apiFetch(`unhotel/v1/suggestions?field=${field}`).then(data => {
        if (Array.isArray(data)) {
          setSuggestions(prev => ({ ...prev, [field]: data }));
        }
      }).catch(err => console.warn(`Suggestion fetch failed for ${field}`, err));
    });
  }, []);

  const handleSaveLogistics = async (data) => {
    if (!selectedGuestId) return;

    // Optimistic Close (UI only)
    setIsLogisticsModalOpen(false);

    // Call API - Safety Logic is now handled centrally in updateGuestAPI
    await updateGuestAPI(selectedGuestId, { logistics: data });
  };

  const selectedDateISO = formatDateISO(selectedDate);
  const getHeaderDate = () => {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const isToday = selectedDateISO === formatDateISO(today);
    const isTomorrow = selectedDateISO === formatDateISO(tomorrow);
    const options = { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' };
    const dateStr = selectedDate.toLocaleDateString(CURRENT_LANG.replace('_', '-'), options);
    if (isToday) return `${t("Today")} ${dateStr.split(',')[1]}`;
    if (isTomorrow) return `${t("Tomorrow")} ${dateStr.split(',')[1]}`;
    return dateStr;
  };

  // --- 1. Pre-fetch 61 Day Window on Mount ---
  useEffect(() => {
    const initFetch = async () => {
      console.log("[App] Starting Cache Prefetch (61 days)...");
      setLoading(true);
      try {
        const today = new Date();
        const start = new Date(today); start.setDate(today.getDate() - 30);
        const end = new Date(today); end.setDate(today.getDate() + 30);

        const startISO = formatDateISO(start);
        const endISO = formatDateISO(end);

        const data = await apiFetch(`unhotel/v1/guests?start_date=${startISO}&end_date=${endISO}`);
        const list = Array.isArray(data) ? data : [];
        console.log(`[App] Prefetch complete. Got ${list.length} guests.`);

        // Bucket by date
        const acc = {};
        list.forEach(g => {
          // Assume g.checkinIso matches our ISO date format
          const d = g.checkinIso; // YYYY-MM-DD
          if (!acc[d]) acc[d] = [];
          acc[d].push(g);
        });

        setGuestCache(prev => ({ ...prev, ...acc }));
        setHasPrefetched(true);

      } catch (err) {
        console.error("[App] Prefetch failed:", err);
      } finally {
        setLoading(false);
      }
    };

    initFetch();
  }, []);

  // --- 2. Load Guests for View (Cache First) ---
  useEffect(() => {
    if (!searchVal) {
      if (hasPrefetched) {
        console.log(`[App] Loading view for ${selectedDateISO} from cache.`);
        const cached = guestCache[selectedDateISO] || [];
        setGuests(cached);
      } else {
        loadGuestsForDate(selectedDateISO);
      }
    }
  }, [selectedDateISO, searchVal, hasPrefetched]); // Removed guestCache to avoid loops, only selectedDate changes matter once prefetched

  const loadGuestsForDate = async (isoDate, forceRefresh = false) => {
    // If not forcing refresh and we have cache (and not prefetch mount, which is handled above), use it.
    // Note: If prefetch is done, cache should be populated.
    if (!forceRefresh && guestCache[isoDate]) {
      console.log(`[App] Cache hit for ${isoDate}`);
      setGuests(guestCache[isoDate]);
      return guestCache[isoDate];
    }

    console.log(`[App] Fetching guests for ${isoDate} (Force: ${forceRefresh})`);
    setLoading(true);
    try {
      const data = await apiFetch(`unhotel/v1/guests?start_date=${isoDate}&end_date=${isoDate}`);
      const list = Array.isArray(data) ? data : [];
      setGuestCache(prev => ({ ...prev, [isoDate]: list }));
      setGuests(list);
      return list;
    } catch (err) {
      console.error(err);
      return [];
    } finally {
      setLoading(false);
    }
  };


  const handleSearch = async (val) => {
    if (!val.trim()) { setSearchVal(''); loadGuestsForDate(selectedDateISO); return; }
    setLoading(true);
    try {
      const data = await apiFetch(`unhotel/v1/guests?search=${encodeURIComponent(val)}`);
      const list = Array.isArray(data) ? data : [];
      setGuests(list);
      if (list.length > 0) {
        setSelectedGuestId(list[0].refId || list[0].id);
        if (list[0].checkinIso) {
          const [y, m, d] = list[0].checkinIso.split('-').map(Number);
          const newDate = new Date(y, m - 1, d);
          setSelectedDate(newDate);
          setCurrentMonth(newDate);
        }
      }
    } catch (err) { console.error(err); } finally { setLoading(false); }
  };

  const refreshCurrent = () => { if (searchVal) handleSearch(searchVal); else loadGuestsForDate(selectedDateISO, true); };

  const handleFilterSelect = (filterId) => {
    // 1. Toggle Logic
    let newFilter = filterId;
    if (activeFilter === filterId && filterId !== 'all') {
      newFilter = 'all';
    }
    setActiveFilter(newFilter);

    // 2. Scroll to Top Logic
    // Target the guest list container
    if (guestListRef.current) {
      guestListRef.current.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  // Last Minute Logic
  // Alert if remote count > local last minute guests
  const lastMinuteGuests = guests.filter(g => isSameDayBooking(g.booking_ts, g.checkinIso || selectedDateISO));
  const isAlerting = remoteLMCount > lastMinuteGuests.length;

  const handleLastMinuteClick = async () => {
    if (isAlerting) {
      // 1. Force Refresh to resolve discrepancy
      setLoading(true);
      const freshList = await loadGuestsForDate(selectedDateISO, true);

      // 2. Select newest LM guest (optional, but good UX)
      // The alert clears automatically because freshList.length should now equal remoteLMCount
      const freshLastMinuteGuests = freshList.filter(g => isSameDayBooking(g.booking_ts, g.checkinIso || selectedDateISO));
      const sortedByNewest = [...freshLastMinuteGuests].sort((a, b) => (b.booking_ts || 0) - (a.booking_ts || 0));
      const targetGuest = sortedByNewest[0];

      if (targetGuest) {
        setSelectedGuestId(targetGuest.refId || targetGuest.id);
        setActiveFilter('last_minute');
      }
    } else {
      setActiveFilter(activeFilter === 'last_minute' ? 'all' : 'last_minute');
    }
  };

  const processedGuests = useMemo(() => {
    let list = [...guests];
    if (activeFilter === 'urgent') {
      list = list.filter(g => !g.hasDocuments || (g.amountPending && parseFloat(g.amountRaw) > 0.01));
    } else if (activeFilter === 'last_minute') {
      list = list.filter(g => isSameDayBooking(g.booking_ts, g.checkinIso || selectedDateISO));
    } else if (activeFilter.startsWith('cat_')) {
      const catId = activeFilter.replace('cat_', '');
      list = list.filter(g => {
        if (!g.categoryIds) return false;
        // categoryIds format: "1;3;8;"
        const ids = g.categoryIds.split(';').filter(Boolean);
        return ids.includes(catId);
      });
    }

    if (statusFilter) {
      list = list.filter(g => g.status === statusFilter);
    }

    // Removed legacy VIP/Departures as requested
    list.sort((a, b) => {
      if (statusSort) {
        const priorityA = statusPriority[a.status] !== undefined ? statusPriority[a.status] : 99;
        const priorityB = statusPriority[b.status] !== undefined ? statusPriority[b.status] : 99;
        if (priorityA !== priorityB) {
           return statusSort === 'asc' ? priorityA - priorityB : priorityB - priorityA;
        }
      }

      let valA, valB;
      switch (sortConfig.key) {
        case 'time': valA = a.time || '23:59'; valB = b.time || '23:59'; break;
        case 'room': valA = a.room || ''; valB = b.room || ''; break;
        case 'name': valA = a.name || ''; valB = b.name || ''; break;
        default: return 0;
      }
      if (valA < valB) return sortConfig.dir === 'asc' ? -1 : 1;
      if (valA > valB) return sortConfig.dir === 'asc' ? 1 : -1;
      return 0;
    });
    return list;
  }, [guests, activeFilter, sortConfig, statusFilter, statusSort]);

  const updateGuestAPI = async (id, payload) => {
    // 1. Snapshot for Rollback
    const previousGuests = [...guests];
    const previousCache = { ...guestCache };

    // 2. Optimistic Update
    setGuests(prev => prev.map(g => (g.refId === id ? { ...g, ...payload } : g)));

    // Optimistic Cache Update (if needed)
    if (!searchVal) {
      setGuestCache(prev => {
        const currentList = prev[selectedDateISO] || [];
        const newList = currentList.map(g => (g.refId === id ? { ...g, ...payload } : g));
        return { ...prev, [selectedDateISO]: newList };
      });
    }

    try {
      // 3. API Call
      console.log(`[updateGuestAPI] Sending update for ${id}...`);
      await apiFetch('unhotel/v1/update', { method: 'POST', body: JSON.stringify({ refId: id, ...payload }) });
      console.log(`[updateGuestAPI] Update success.`);
      setToastMessage({ type: 'success', msg: t("Saved successfully") });

    } catch (err) {
      console.error("[updateGuestAPI] CAUGHT ERROR:", err);

      // 5. Rollback on Error
      console.log("[updateGuestAPI] Rolling back state...");
      setGuests(previousGuests);
      setGuestCache(previousCache);
      // Force a new object for toast to ensure re-render?
      setToastMessage({ type: 'error', msg: t("Error saving details"), id: Date.now() });
    }
  };
  const handleStatusChange = (status) => { if (selectedGuest) updateGuestAPI(selectedGuest.refId, { status }); };
  const addNote = () => {
    if (!selectedGuest || !noteInput.trim()) return;
    const now = new Date();
    const newNote = {
      text: noteInput,
      time: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      timestamp: now.getTime(),
      type: 'human',
      author: USER_NAME,
      id: Date.now(),
      isStarred: false
    };
    const updatedNotes = [...(selectedGuest.notes || []), newNote];
    updateGuestAPI(selectedGuest.refId, { notes: updatedNotes });
    setNoteInput('');
  };

  const toggleStar = (noteId) => {
    if (!selectedGuest) return;
    const updatedNotes = (selectedGuest.notes || []).map(n => {
      // Handle legacy notes that might not have an ID by skipping star toggle or assigning one if we could, 
      // but for now only new notes or notes with ID can be starred.
      if (n.id === noteId) return { ...n, isStarred: !n.isStarred };
      return n;
    });
    updateGuestAPI(selectedGuest.refId, { notes: updatedNotes });
  };
  const copyToClipboard = (guest, e) => {
    e.stopPropagation();
    
    const parseSafeDate = (ts) => {
      if (!ts) return new Date();
      return new Date(ts * 1000);
    };
    
    const cin = parseSafeDate(guest.checkin);
    const cout = parseSafeDate(guest.checkout);
    
    const pad = (n) => n.toString().padStart(2, '0');
    const cinStr = `${pad(cin.getDate())}/${pad(cin.getMonth() + 1)}`;
    const coutStr = `${pad(cout.getDate())}/${pad(cout.getMonth() + 1)}/${cout.getFullYear()}`;
    
    const firstName = guest.fullName ? guest.fullName.split(' ')[0] : guest.name;
    const channel = guest.source || guest.otaRef || 'Website';
    const textToCopy = `${firstName} ${guest.room} ${cinStr} - ${coutStr} ${channel} ${guest.bookingId}`;
    
    navigator.clipboard.writeText(textToCopy);
    setCopiedId(guest.refId);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const selectedGuest = guests.find(g => g.refId === selectedGuestId) || null;
  const daysInMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0).getDate();
  const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1).getDay();
  const startOffset = firstDay === 0 ? 6 : firstDay - 1;

  return (
    <div className="flex lg:grid lg:grid-cols-[280px_1fr_400px] h-screen w-full max-w-[1600px] mx-auto bg-gray-50 text-slate-800 font-sans overflow-hidden">
      {/* Toast Notification */}
      {toastMessage && (
        <div className={`fixed top-4 right-4 z-[9999] p-4 rounded shadow-lg text-white font-bold animate-in slide-in-from-top-5 duration-300 ${toastMessage.type === 'error' ? 'bg-red-500' : 'bg-emerald-500'}`}>
          {toastMessage.msg}
        </div>
      )}
      {/* Mobile Sidebar Backdrop */}
      {isSidebarOpen && (
        <div
          className="fixed inset-0 bg-black/40 z-[55] lg:hidden backdrop-blur-sm transition-opacity"
          onClick={() => setIsSidebarOpen(false)}
        />
      )}

      {/* Sidebar Container */}
      <div className={`fixed inset-y-0 left-0 z-[60] w-[280px] bg-white border-r border-gray-200 flex flex-col h-full overflow-y-auto transition-transform duration-300 transform ${isSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0 lg:shadow-none'} lg:static lg:flex lg:shrink-0`}>
        {/* Mobile Close Button */}
        <div className="lg:hidden absolute top-2 right-2 z-10">
          <button onClick={() => setIsSidebarOpen(false)} className="p-2 text-slate-400 hover:text-slate-600 bg-white/50 rounded-full">
            <SafeIcon icon={X} size={20} />
          </button>
        </div>
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center gap-2">
            {window.unhotelData?.logoUrl ? (
              <img src={window.unhotelData.logoUrl} alt="Unhotel" className="h-10 w-auto" />
            ) : (
              <h1 className="text-xl font-bold text-[#222222] flex items-center gap-2">
                <div className="w-8 h-8 bg-[#FF4F7C] rounded-full flex items-center justify-center text-white"><SafeIcon icon={LogIn} size={18} /></div>
                FrontDesk
              </h1>
            )}
          </div>
          <h2 className="text-[#222222] font-bold text-lg pt-2 mt-1">{t("Reception Dashboard")}</h2>
        </div>
        <div className="p-4 flex-1 overflow-y-auto">
          <div className="mb-6 relative">
            <SafeIcon icon={Search} className="absolute right-3 top-2.5 text-gray-400 pointer-events-none" size={16} />
            <input type="text" placeholder={t("Search guest...")} value={searchVal} onChange={(e) => setSearchVal(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch(searchVal)} className="w-full pl-3 pr-10 py-2 bg-white card-soft text-sm focus:ring-2 focus:ring-[#FF4F7C]/20 focus:border-[#FF4F7C] outline-none" />
            {searchVal && <button onClick={() => { setSearchVal(''); handleSearch(''); }} className="absolute right-8 top-2.5 text-gray-400 hover:text-gray-600"><SafeIcon icon={X} size={16} /></button>}
          </div>
          <div className="mb-6">
            <div className="bg-white border border-gray-200 rounded-xl p-3 shadow-sm mb-6">
              <div className="flex justify-between items-center mb-4">
                <span className="font-semibold text-sm capitalize">{currentMonth.toLocaleString(CURRENT_LANG.replace('_', '-'), { month: 'long', year: 'numeric' })}</span>
                <div className="flex gap-1">
                  <button onClick={() => setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1))} className="p-1 hover:bg-gray-100 rounded"><SafeIcon icon={ChevronRight} className="rotate-180" size={14} /></button>
                  <button onClick={() => setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1))} className="p-1 hover:bg-gray-100 rounded"><SafeIcon icon={ChevronRight} size={14} /></button>
                </div>
              </div>
              <div className="grid grid-cols-7 gap-1 text-center text-xs text-gray-500 mb-2">
                {[t("Mon_S"), t("Tue_S"), t("Wed_S"), t("Thu_S"), t("Fri_S"), t("Sat_S"), t("Sun_S")].map((day, i) => (
                  <span key={i}>{day}</span>
                ))}
              </div>
              <div className="grid grid-cols-7 gap-1 text-center text-xs font-medium">
                {[...Array(startOffset)].map((_, i) => <div key={`empty-${i}`} />)}
                {[...Array(daysInMonth)].map((_, i) => {
                  const day = i + 1;
                  const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), day);
                  const isSelected = formatDateISO(date) === selectedDateISO;
                  const isToday = formatDateISO(date) === formatDateISO(new Date());
                  return <button key={day} onClick={() => setSelectedDate(date)} className={`h-7 w-7 rounded-full flex flex-col items-center justify-center transition-all relative ${isSelected ? 'bg-[#FF4F7C] text-white shadow-md shadow-[#FF4F7C]/40' : 'hover:bg-gray-100 text-slate-700'} ${isToday && !isSelected ? 'text-[#FF4F7C] font-bold bg-[#FF4F7C]/10' : ''}`}>{day}</button>;
                })}
              </div>
            </div>
            <LastMinuteWidget
              active={activeFilter === 'last_minute'}
              onClick={handleLastMinuteClick}
              isAlerting={isAlerting}
              onCountChange={setRemoteLMCount}
            />
          </div>
          <div className="mb-6">
            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{t("Sort By")}</h3>
            {/* Sort Buttons */}
            <div className="flex bg-gray-100 rounded-lg p-1">
              {['Time', 'Room', 'Name'].map(key => (
                <button key={key} onClick={() => setSortConfig(prev => ({ key: key.toLowerCase(), dir: prev.key === key.toLowerCase() && prev.dir === 'asc' ? 'desc' : 'asc' }))} className={`flex-1 flex items-center justify-center gap-1 py-1.5 text-xs font-medium rounded capitalize ${sortConfig.key === key.toLowerCase() ? 'bg-white shadow-sm text-[#FF4F7C]' : 'text-gray-500 hover:text-gray-700'}`}>
                  {t(key)}
                  {sortConfig.key === key.toLowerCase() && (sortConfig.dir === 'asc' ? <SafeIcon icon={ArrowUp} size={10} /> : <SafeIcon icon={ArrowDown} size={10} />)}
                </button>
              ))}
            </div>
          </div>
          <div className="space-y-1">
            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{t("Quick Filters")}</h3>

            {/* Standard Filters */}
            <div className="space-y-1 mb-4">
              <button onClick={() => handleFilterSelect('all')} className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all ${activeFilter === 'all' ? 'bg-[#FF4F7C]/10 text-[#FF4F7C] font-medium' : 'text-slate-600 hover:bg-gray-50'}`}>
                <div className="flex items-center gap-3"><SafeIcon icon={LogIn} size={16} className={activeFilter === 'all' ? 'text-[#FF4F7C]' : 'text-gray-400'} />{t("All Arrivals")}</div>
                <span className={`text-xs px-2 py-0.5 rounded-full ${activeFilter === 'all' ? 'bg-[#FF4F7C]/20 text-[#FF4F7C]' : 'bg-gray-100'}`}>{guests.length}</span>
              </button>

              <button onClick={() => handleFilterSelect('urgent')} className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all ${activeFilter === 'urgent' ? 'bg-[#FF4F7C]/10 text-[#FF4F7C] font-medium' : 'text-slate-600 hover:bg-gray-50'}`}>
                <div className="flex items-center gap-3"><SafeIcon icon={AlertCircle} size={16} className={activeFilter === 'urgent' ? 'text-[#FF4F7C]' : 'text-gray-400'} />{t("Action Required")}</div>
                <span className={`text-xs px-2 py-0.5 rounded-full ${activeFilter === 'urgent' ? 'bg-[#FF4F7C]/20 text-[#FF4F7C]' : 'bg-gray-100'}`}>
                  {guests.filter(g => !g.hasDocuments || (g.amountPending && parseFloat(g.amountRaw) > 0.01)).length}
                </span>
              </button>
            </div>

            {/* Dynamic Category Groups */}
            <div className="space-y-2">
              {filterGroups.map(group => {
                const isExpanded = expandedGroups[group.id];
                const groupItems = group.items || [];
                // Calculate total matched guests within group? Or just per item. Request says per Sub-item.

                return (
                  <div key={group.id} className="border-t border-gray-100 pt-2">
                    <button
                      onClick={() => setExpandedGroups(prev => ({ ...prev, [group.id]: !prev[group.id] }))}
                      className="w-full flex items-center justify-between px-2 py-1 text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-wider"
                    >
                      {group.label}
                      <SafeIcon icon={ChevronRight} size={12} className={`transition-transform duration-200 ${isExpanded ? 'rotate-90' : ''}`} />
                    </button>

                    {isExpanded && (
                      <div className="mt-1 space-y-1 pl-2 animate-in slide-in-from-top-1 duration-200">
                        {groupItems.map(cat => {
                          const catId = String(cat.id);
                          const filterId = `cat_${catId}`;
                          // Dynamic Count
                          const count = guests.filter(g => g.categoryIds && g.categoryIds.split(';').filter(Boolean).includes(catId)).length;

                          return (
                            <button
                              key={cat.id}
                              onClick={() => handleFilterSelect(filterId)}
                              className={`w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-sm transition-all ${activeFilter === filterId ? 'bg-[#FF4F7C]/10 text-[#FF4F7C] font-medium' : 'text-slate-600 hover:bg-gray-50'}`}
                            >
                              <span className="truncate">{cat.name}</span>
                              {count > 0 && <span className={`text-xs px-1.5 py-0.5 rounded-full min-w-[20px] text-center ${activeFilter === filterId ? 'bg-[#FF4F7C]/20' : 'bg-gray-100'}`}>{count}</span>}
                            </button>
                          );
                        })}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>

            <div className="mt-4 space-y-2">
              <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{t("Status")}</h3>
              <div className="space-y-1">
                {Object.keys(statusPriority).map(statusName => {
                  const count = guests.filter(g => g.status === statusName).length;
                  const isActive = statusFilter === statusName;
                  return (
                    <button
                      key={statusName}
                      onClick={() => setStatusFilter(isActive ? null : statusName)}
                      className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all ${isActive ? 'bg-[#FF4F7C]/10 text-[#FF4F7C] font-medium' : 'text-slate-600 hover:bg-gray-50'}`}
                    >
                      <div className="flex items-center gap-3">
                        <span className="truncate">{statusName}</span>
                      </div>
                      {count > 0 && <span className="text-xs px-2 py-0.5 rounded-full text-gray-800" style={{ backgroundColor: window.unhotelData?.statusColors?.[statusName] || '#f3f4f6' }}>{count}</span>}
                    </button>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="flex-1 flex flex-col min-w-0 bg-white border-r border-gray-200 h-full overflow-hidden">
        <div className="h-16 border-b border-gray-100 flex items-center justify-between px-6 bg-white sticky top-0 z-30">
          <div className="flex items-center gap-3">
            {/* Hamburger Button (Mobile Only) */}
            <button
              onClick={() => setIsSidebarOpen(true)}
              className="lg:hidden p-2 -ml-2 text-slate-500 hover:bg-gray-100 rounded-lg transition-colors"
              title={t("Open Menu")}
            >
              <SafeIcon icon={Menu} size={24} />
            </button>
            <div>
              <h2 className="text-lg font-bold text-slate-800">{getHeaderDate()}</h2>
              <p className="text-xs text-slate-500">{guests.length} {t("Arrivals")}</p>
            </div>
          </div>
          <button onClick={refreshCurrent} className={`p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-indigo-600 transition-all ${loading ? 'animate-spin' : ''}`} title={t("Refresh List")}><SafeIcon icon={RefreshCw} size={18} /></button>
        </div>
        <div ref={guestListRef} className="flex-1 overflow-y-auto bg-gray-50/50 relative">
          <div className="sticky top-0 z-20 bg-gray-50/95 backdrop-blur-sm border-b border-gray-200 flex items-center justify-between px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
            <span className="w-14 text-center">{t("Time")}</span>
            <span className="flex-1 ml-4">{t("Guest Details")}</span>
            <span 
              className="w-24 text-center cursor-pointer hover:text-indigo-600 transition-colors flex items-center justify-center gap-1"
              onClick={() => setStatusSort(prev => prev === 'asc' ? 'desc' : 'asc')}
            >
              {t("Status")}
              {statusSort && (statusSort === 'asc' ? <SafeIcon icon={ArrowUp} size={10} /> : <SafeIcon icon={ArrowDown} size={10} />)}
            </span>
          </div>
          <div className="p-4 space-y-3">
            {loading && guests.length === 0 ? <div className="text-center py-10 text-gray-400 text-sm">{t("Loading...")}</div> : processedGuests.length === 0 ? <div className="text-center py-10 text-gray-400 text-sm">{t("No arrivals found for this view.")}</div> : processedGuests.map(guest => {
              const isActive = selectedGuestId === guest.refId;
              console.warn('GUEST PIPELINE:', guest.name, '| Starred:', guest.has_starred_note, '| Notes:', guest.notes);
              return (
                <div key={guest.refId} onClick={() => setSelectedGuestId(guest.refId)} className={`group relative flex flex-col lg:grid lg:grid-cols-[100px_1fr_100px_150px] lg:items-center p-3 gap-4 w-full card-soft transition-all cursor-pointer hover:shadow-md bg-white mb-2 ${isActive ? 'ring-2 ring-[#FF4F7C]/50' : ''}`}>
                  <div className="w-full lg:w-auto flex flex-row lg:flex-col items-center justify-center lg:justify-center border-b lg:border-b-0 lg:border-r border-gray-100 pb-2 lg:pb-0 lg:pr-4 self-center lg:self-auto text-center lg:text-left" onClick={(e) => e.stopPropagation()}><InlineTimeEdit time={guest.time} onSave={(newTime) => updateGuestAPI(guest.refId, { time: newTime })} /></div>
                  <div className="min-w-0 w-full pt-2 lg:pt-0">
                    <div className="flex flex-col sm:flex-row items-start justify-between gap-2 w-full">
                      <div className="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <span className="font-bold text-base text-slate-700">{guest.room}</span>
                        <GuestAvatar guest={guest} />
                        <div>
                          {/* N19 N2 N3 */}
                          <div className="flex items-center gap-2 mb-0.5">
                            {guest.flag && <img src={guest.flag} alt="flag" className="w-5 h-5 rounded-full object-cover shadow-sm" title={guest.country} />}

                            <div className="flex items-center gap-1">
                              <h3 className={`text-sm font-bold truncate ${isActive ? 'text-indigo-700' : 'text-slate-900'}`}>{guest.name}</h3>
                              {guest.has_starred_note && (
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-amber-400">
                                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                              )}
                            </div>
                            {isSameDayBooking(guest.booking_ts, guest.checkinIso || selectedDateISO) && <SafeIcon icon={Zap} size={14} className="text-orange-500 fill-orange-500 animate-pulse" title={t("Last Minute (Same Day Booking)")} />}
                            {guest.hasDocuments ? <SafeIcon icon={FileText} size={14} className="text-emerald-500" title={t("Documents Checked")} /> : <SafeIcon icon={AlertCircle} size={14} className="text-red-400" title={t("Missing Documents")} />}
                            <button onClick={(e) => copyToClipboard(guest, e)} className={`transition-opacity ${copiedId === guest.refId ? 'opacity-100 text-emerald-500' : 'opacity-0 group-hover:opacity-100 text-gray-300 hover:text-indigo-500'}`}>
                              <SafeIcon icon={copiedId === guest.refId ? Check : Copy} size={12} />
                            </button>
                          </div>
                          {/* N4 N5 N6 N11 */}
                          <div className="text-xs text-slate-400 font-medium flex items-center gap-3">
                            <span className="flex items-center gap-1"><SafeIcon icon={Users} size={12} /> {guest.pax}</span>
                            <span className="flex items-center gap-1"><SafeIcon icon={Moon} size={12} /> {guest.nights}n</span>
                            <span className="uppercase tracking-wider text-[10px] font-bold text-gray-300 flex items-center gap-1">
                              {guest.source}
                              {guest.otaRef && <span className="font-normal text-gray-400">({guest.otaRef})</span>}
                            </span>
                          </div>
                          {guest.amountPending && parseFloat(guest.amountRaw) > 0.01 && <span className="text-red-600 font-bold bg-red-50 px-1 rounded text-sm">{t("Due")}: {formatCurrency(guest.amountRaw)}</span>}
                        </div>
                      </div>

                      <div className="flex flex-col items-start sm:items-end gap-1">
                        {guest.logistics && guest.logistics.transportType && (
                          <div className="flex items-center gap-1 text-[10px] text-slate-500 font-medium bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 mt-1">
                            {guest.logistics.transportType === 'plane' ? <SafeIcon icon={Plane} size={10} /> :
                              guest.logistics.transportType === 'car' ? <SafeIcon icon={Car} size={10} /> :
                                guest.logistics.transportType === 'bus' ? <SafeIcon icon={Bus} size={10} /> :
                                  <SafeIcon icon={Footprints} size={10} />}
                            <span>
                              {guest.logistics.transportType === 'plane'
                                ? `${guest.logistics.flightNumber || 'FLT'} • ${guest.logistics.landingTime || '--:--'}`
                                : (guest.logistics.arrivingFrom || guest.logistics.transportType)}
                              {guest.logistics.nextDay && <span className="text-red-400 ml-0.5">(+1)</span>}
                            </span>
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="mt-2 lg:mt-0 w-full flex items-center justify-center lg:justify-start shrink-0 self-center lg:self-auto text-center lg:text-left">
                    <span className="font-extrabold text-slate-800 text-sm bg-slate-100 px-1.5 rounded">#{guest.bookingId}</span>
                  </div>
                  <div className="mt-2 lg:mt-0 w-full flex items-center justify-center lg:justify-end lg:justify-self-end shrink-0 self-center lg:self-auto text-center lg:text-left">
                    <RegistrationStatusDropdown
                      currentStatus={guest.status}
                      lookups={lookups['registration_status'] || []}
                      onChange={(newStatus) => updateGuestAPI(guest.refId, { status: newStatus })}
                    />
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
      <div className={`fixed top-0 right-0 bottom-0 h-[100dvh] w-full md:w-[400px] lg:w-auto lg:static lg:h-full flex flex-col lg:overflow-hidden bg-white shadow-2xl lg:shadow-none z-50 border-l border-gray-200 transition-transform duration-300 transform ${selectedGuest ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'}`}>
        {selectedGuest ? (
          <>
            {/* Close Button (Mobile/Tablet Only) - ALWAYS VISIBLE */}
            <button
              onClick={() => setSelectedGuestId(null)}
              className="lg:hidden absolute top-4 right-4 z-[60] p-2 bg-black/40 hover:bg-black/60 text-white rounded-full backdrop-blur-sm transition-colors shadow-sm"
              title={t("Close Details")}
            >
              <SafeIcon icon={X} size={20} />
            </button>

            {/* Single Scrollable Container */}
            <div className="flex-1 overflow-y-auto min-h-0 w-full relative pb-20">
              <div className="h-40 bg-gray-200 relative flex-shrink-0 overflow-hidden group">
                {/* Dual-Check Room Image URL */}
                {selectedGuest.roomImageUrl ? (
                  <img src={selectedGuest.roomImageUrl} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                ) : (
                  <div className="w-full h-full bg-gradient-to-r from-slate-900 to-slate-800"></div>
                )}
                <div className="absolute top-4 right-4 flex gap-2 hidden lg:flex">
                  {/* Desktop Actions */}
                  <button className="p-1.5 bg-white/10 hover:bg-white/20 text-white rounded backdrop-blur-sm"><SafeIcon icon={MoreHorizontal} size={18} /></button>
                </div>
              </div>

              <div className="px-6 relative -mt-10 mb-6">
                <div className="flex justify-between items-end">
                  <div className="bg-white p-1 rounded-full shadow-lg">
                    <div className="w-20 h-20 rounded-full overflow-hidden bg-slate-100">{selectedGuest.pic ? <img src={selectedGuest.pic} className="w-full h-full object-cover" /> : <div className="w-full h-full flex items-center justify-center text-slate-300"><SafeIcon icon={User} size={32} /></div>}</div>
                  </div>
                  <div className="flex flex-col items-end gap-2 pb-1">
                    {isSameDayBooking(selectedGuest.booking_ts, selectedGuest.checkinIso || selectedDateISO) && (
                      <span className="bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide shadow-sm">
                        {t("Same Day")}
                      </span>
                    )}
                    <RegistrationStatusDropdown
                      currentStatus={selectedGuest.status}
                      lookups={lookups['registration_status'] || []}
                      onChange={(newStatus) => updateGuestAPI(selectedGuest.refId, { status: newStatus })}
                    />
                  </div>
                </div>
                <div className="mt-3">
                  <h1 className="text-xl font-bold text-slate-900 flex items-center gap-2">{selectedGuest.fullName}{selectedGuest.flag && <img src={selectedGuest.flag} className="w-5 h-5 rounded-full border border-gray-100" />}</h1>
                  <div className="flex flex-col gap-1 mt-2 text-sm text-gray-500">
                    <span className="flex items-center gap-2"><SafeIcon icon={Mail} size={14} /> {selectedGuest.email || t("No email")}</span>
                    <span className="flex items-center gap-2"><SafeIcon icon={Phone} size={14} /> {selectedGuest.phone || t("No phone")} {selectedGuest.whatsapp && <a href={selectedGuest.whatsapp} target="_blank" className="text-emerald-600 text-xs font-bold hover:underline">WhatsApp</a>}</span>
                  </div>

                  {/* Last Minute Warning */}
                  {isSameDayBooking(selectedGuest.booking_ts, selectedGuest.checkinIso || selectedDateISO) && (
                    <div className="mt-4 bg-orange-50 border border-orange-100 rounded-lg p-3 flex gap-3">
                      <div className="flex-shrink-0 mt-0.5">
                        <div className="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center text-white">
                          <SafeIcon icon={Zap} size={12} fill="currentColor" />
                        </div>
                      </div>
                      <div>
                        <h4 className="text-orange-900 font-bold text-xs uppercase tracking-wide">{t("Last Minute Booking")}</h4>
                        <p className="text-orange-800 text-xs mt-0.5 leading-relaxed">
                          {t("This reservation was made today. Ensure the room is clean and payment is secured before check-in.")}
                        </p>
                      </div>
                    </div>
                  )}
                </div>
                <div className="mt-6">
                  <a href={selectedGuest.resLink} target="_blank" className={`flex items-center justify-center gap-2 w-full py-3 btn-primary shadow-sm transition-all hover:shadow-md active:scale-95 text-white hover:text-white ${selectedGuest.hasDocuments ? 'bg-[#FF4F7C]' : 'bg-[#FF4F7C]'}`}>
                    {selectedGuest.hasDocuments ? <SafeIcon icon={CheckCircle2} size={18} /> : <SafeIcon icon={AlertCircle} size={18} />}
                    {t("Registration / Check-in")}
                  </a>
                </div>

                <div className="grid grid-cols-2 gap-4 mt-6 bg-slate-50 border border-slate-100 rounded-xl p-4">
                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Apartment")}</p>
                    <p className="text-sm font-bold text-slate-800">{selectedGuest.room}</p>
                  </div>
                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Reservation #")}</p>
                    <p className="text-sm font-bold text-slate-800">#{selectedGuest.resId || selectedGuest.bookingId}</p>
                  </div>

                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Check-In")}</p>
                    <p className="text-sm font-bold text-slate-800">{new Date(selectedGuest.checkinIso.replace(/-/g, '/')).toLocaleDateString('en-GB')}</p>
                  </div>
                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Check-Out")}</p>
                    <p className="text-sm font-bold text-slate-800">
                      {(() => {
                        if (!selectedGuest.checkout) return t("N/A");
                        const d = new Date(Number(selectedGuest.checkout) * 1000);
                        return isNaN(d.getTime()) ? t("Invalid Date") : d.toLocaleDateString('en-GB');
                      })()}
                    </p>
                  </div>


                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Duration")}</p>
                    <p className="text-sm font-bold text-slate-800">{selectedGuest.nights} {t("Nights")}</p>
                  </div>
                  <div>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{t("Balance")}</p>
                    <p className={`text-sm font-bold ${selectedGuest.amountPending ? 'text-red-500' : 'text-emerald-500'}`}>
                      {selectedGuest.amountPending ? formatCurrency(selectedGuest.amountRaw) + ` ${t("Due")}` : t("Paid")}
                    </p>
                  </div>
                </div>
              </div>

              {/* Activity Timeline Section (Inside Scroll) */}
              <div className="px-6 pb-20">
                <ArrivalLogisticsCard guest={selectedGuest} onEdit={() => setIsLogisticsModalOpen(true)} />

                <h3 className="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2"><SafeIcon icon={MessageSquare} size={16} className="text-[#FF4F7C]" />{t("Activity Timeline")}</h3>
                <div className="space-y-6 relative ml-2">
                  <div className="absolute left-[5px] top-2 bottom-0 w-0.5 bg-gray-200"></div>
                  {(!selectedGuest.notes || selectedGuest.notes.length === 0) && <p className="text-xs text-gray-400 pl-6 italic">{t("No activity recorded.")}</p>}
                  {(selectedGuest.notes || [])
                    .sort((a, b) => {
                      // 1. Starred Priority
                      if (a.isStarred && !b.isStarred) return -1;
                      if (!a.isStarred && b.isStarred) return 1;
                      // 2. Timestamp Descending (Newest First)
                      const tA = a.timestamp || 0;
                      const tB = b.timestamp || 0;
                      return tB - tA;
                    })
                    .map((note, idx) => {
                      const noteDate = note.timestamp ? new Date(note.timestamp > 1e11 ? note.timestamp : note.timestamp * 1000) : null;
                      const displayTime = noteDate
                        ? `${String(noteDate.getDate()).padStart(2, '0')}/${String(noteDate.getMonth() + 1).padStart(2, '0')}/${noteDate.getFullYear()} ${String(noteDate.getHours()).padStart(2, '0')}:${String(noteDate.getMinutes()).padStart(2, '0')}`
                        : note.time;

                      return (
                        <div key={idx} className="relative pl-6">
                          <div className={`absolute left-0 top-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm z-10 ${note.type === 'human' ? 'bg-[#FF4F7C]' : 'bg-gray-400'}`}></div>
                          <div className={`bg-white p-3 rounded-lg border shadow-sm transition-all group ${note.isStarred ? 'border-amber-300 ring-1 ring-amber-100' : 'border-gray-100'}`}>
                            <div className="flex justify-between items-start gap-2">
                              <p className="text-sm text-slate-700 flex-1">
                                {note.text.startsWith('Changed status from') ? (
                                  <span>
                                    {t("Changed status from registration to")} <strong>{note.text.split(' to ')[1]}</strong>
                                  </span>
                                ) : note.text}
                              </p>
                              {note.id && (
                                <button
                                  onClick={(e) => { e.stopPropagation(); toggleStar(note.id); }}
                                  className={`flex-shrink-0 transition-colors ${note.isStarred ? 'text-amber-400 fill-amber-400' : 'text-gray-300 hover:text-amber-400'}`}
                                >
                                  <SafeIcon icon={Star} size={14} fill={note.isStarred ? "currentColor" : "none"} />
                                </button>
                              )}
                            </div>
                            <div className="flex justify-between items-center mt-1.5">
                              <span className="text-[10px] text-gray-400 font-medium">{note.author || t("System")}</span>
                              <span className="text-[10px] text-gray-300 font-mono">{displayTime}</span>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                </div>
                <div className="p-4 bg-gray-50 border-t border-gray-200 mt-6 rounded-lg">
                  <div className="relative"><input type="text" value={noteInput} onChange={(e) => setNoteInput(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && addNote()} placeholder={t("Add a note...")} className="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#FF4F7C] outline-none" /><button onClick={addNote} className="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-[#FF4F7C]"><SafeIcon icon={ChevronRight} size={18} /></button></div>
                </div>
              </div>

            </div>
          </>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center text-gray-400"><SafeIcon icon={User} size={48} className="mb-4 opacity-20" /><p>{t("Select a guest to view details")}</p></div>
        )
        }
      </div >

      <ArrivalLogisticsModal
        guest={selectedGuest}
        isOpen={isLogisticsModalOpen}
        onClose={() => setIsLogisticsModalOpen(false)}
        onSave={handleSaveLogistics}
        lookups={lookups}
        suggestions={suggestions}
      />
    </div >
  );
}

// Mounting Logic
const rootElement = document.getElementById('unhotel-dashboard-root');
if (rootElement) {
  try {
    let createRoot = null;
    let render = null;
    if (typeof wp !== 'undefined' && wp.element) {
      createRoot = wp.element.createRoot;
      render = wp.element.render;
    }
    if (createRoot) {
      createRoot(rootElement).render(<ErrorBoundary><App /></ErrorBoundary>);
    } else if (render) {
      render(<ErrorBoundary><App /></ErrorBoundary>, rootElement);
    } else {
      if (window.ReactDOM && window.ReactDOM.render) {
        window.ReactDOM.render(<ErrorBoundary><App /></ErrorBoundary>, rootElement);
      } else {
        console.error("React createRoot and ReactDOM not found.");
        rootElement.innerHTML = '<div style="color:red; margin:20px;">Error: React environment not initialized. Update WordPress or check console.</div>';
      }
    }
  } catch (e) {
    console.error("Mount error:", e);
    rootElement.innerHTML = `<div style="color:red; padding: 20px;">Mount Critical Error: ${e.message}</div>`;
  }
}