# CHANGELOG

## 2.2.3
*Release date - 17 July 2025*

* Added the Restricted Key to support the Identity service


## 2.2.2
*Release date - 2 July 2025*

* Added the transaction type parameter to let customers choose the type of transaction: 'pay', 'book', or 'subscribe'. 


## 2.2.1
*Release date - 19 June 2025*

* The customer is now saved in Stripe even when using the Virtual Terminal.

## 2.2.0
*Release date - 9 June 2025*

* Implemented Off-Session payments.

## 2.0.6
*Release date - 4 July 2024*

* Improvement on the Stripe customer creation for the Stripe Session to be created.
* Fixed issue with currency with decimals

## 2.0.1

*Release date - 14 June 2024*

* Fixed a Vik Rent Car minor bug.

---


## 2.0

*Release date - 12 June 2024*

* Improved the Stripe Session creating.
* Improved the order validating process through the API system Stripe made available. 

---

## 1.2.13

*Release date - 17 October 2023*

* Minor improvements applied to the Virtual Terminal.

---

## 1.2.12

*Release date - 4 April 2023*

* Improved validation with currency conversion.

---

## 1.2.11

*Release date - 17 July 2023*

* Stripe v1.2.11 with support for 3DS Import and direct charges for CC

---

## 1.2.10

*Release date - 2 May 2023*

* Removed deprecated code for VikAppointments and VikRestaurants.

---

## 1.2.9

*Release date - 9 February 2023*

* Fixed type check to unset undesidered options.

---

## 1.2.8

*Release date - 13 October 2022*

* Fixed usage of language definition for deposit in VRC.

---

## 1.2.7

*Release date - 5 October 2022*

* Added new setting for currencies with no decimals.
* Removed deprecated cart item details in official SDK.

---

## 1.2.6

*Release date - 16 May 2022*

* Added support for automatic payment methods, even for those who are not reusable, like Klarna.

---

## 1.2.5

*Release date - 18 November 2021*

* Solved a critical issue during payment validation.

---

## 1.2.4

*Release date - 16 November 2021*

* Solved issues with VikAppointments and VikRestaurants due to their updated payment framework. 
* Updated Stripe SDK library to 7.100.

---

## 1.2.3

*Release date - 5 July 2021*

* Enhanced transients time duration in order to avoid issues. 

---

## 1.2.2

*Release date - 30 June 2021*

* Updated Stripe SDK library to 7.85.
* Minor fixes.

---

## 1.2.1

*Release date - 14 June 2021*

* New APIs implemented due to compatibility with PHP 8.0.x

---

## 1.2

*Release date - 7 April 2021*

* Added new Refund APIs.
* Added the possibility to request card authorization and not only direct payment.
* In Stripe's order list, now the transaction name will be displayed correctly, and it will not display the payment ID (Only for future reservations). 
* Bug fixes.
* Improvements.

---

## 1.1.9

*Release date - 17 February 2021*

* Added new parameter to skip automatically the payment button.

---

## 1.1.8

*Release date - 3 February 2021*

* Added authorization and update for SCA compliance

---

## 1.1.7

*Release date - 20 November 2020*

* Fixed issue with updated btn.

---

## 1.1.6

*Release date - 17 November 2020*

* Fixed compatibility issue with VikAppointments.

---

## 1.1.5

*Release date - 02 November 2020*

* Added compatibility with VikRentItems and VikRestaurants.
* Solved issue with amount checking.

---

## 1.1.4

*Release date - 23 March 2020*

* Updated and enhanced transients management and deletion.

---

## 1.1.3

*Release date - 18 February 2020*

* Updated check on amount validation: casting now everything to string in order not to miss any control.
 
---

## 1.1.2

*Release date - 30 January 2020*

* Fixed fatal error in VBO and VRC overrides: was using $this instead of $payments when creating the data file. 

---

## 1.1.1

*Release date - 29 November 2019*

* Added support for VRC
* Fixed issue with transients. 

---

## 1.1

*Release date - 29 July 2019*

* Cart totally removed.

---

## 1.0.9

*Release date - 25 July 2019*

* Added logging system to track each reservation. 

---

## 1.0.8

*Release date - 22 July 2019*

* Simplified cart (Removed precise objects due to wrong each total calculation and taxes calculation).

---

## 1.0.7

*Release date - 19 July 2019*

* Added PO and MO files.
* Fixed issues.

---

## 1.0.6

*Release date - 8 July 2019*

* Updated APIs due to new SCA compliance. 
* Added multilanguage support.

---

## 1.0.5

*Release date - 17 May 2019*

* It is now possible to use Stripe with VikAppointments.
* Added stripe logo for VikBooking (front-end usage).

---

## 1.0.4

*Release date - 19 April 2019*

* Minor fixes applied to the amount validation.

---

## 1.0.3

*Release date - 13 February 2019*

* Minor fixes.

---

## 1.0.2

*Release date - 27 June 2018*

* Fixed an issue that was creating a wrong base path for the plugin.

---

## 1.0.1

*Release date - 27 June 2018*

* Stripe is now able to receive updates through VikUpdater.

---

## 1.0

*Release date - 30 April 2018*

* First release.