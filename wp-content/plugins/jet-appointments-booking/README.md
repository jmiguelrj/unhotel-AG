# ChangeLog

## 2.2.6
* ADD: Filter `jet-apb/workflows/events/can-dispatch` for appointments group created event;
* ADD: `Appointment User Name` macro;
* ADD: Service and provider title to Appointment Date args for webhook;
* FIX: Sync events from Google Calendar with events for several days;
* FIX: Select timezones picker styles in AJAX popup;
* FIX: Create Sample Page Booking Form Toggle doesn't work.

## 2.2.5
* ADD: Use custom timezones for the calendar option;
* ADD: Check for day availability for recurring slots in admin calendar;
* ADD: Switcher with the ability to disable the appointment-created event for a group of appointments;
* FIX: Meta is not sent correctly with webhook;
* FIX: Incorrect calculation of capacity in the form.

## 2.2.4
* FIX: Two booking forms on the same page error;
* FIX: Tokens meta data is not created when Creating Appointments from Admin;
* FIX: Appointments order is lost in the calendar view on the backend;
* FIX: User timezone not recalculated after admin appointment update;
* FIX: Undefined array key 0 on change capacity;
* FIX: Appointment Date error with provider field.

## 2.2.3
* ADD: Remove filter buttons if no filters checked in admin appointments;
* FIX: Scheduled events in workflows don't work;
* FIX: Inherit service price doesn't work for new providers;
* FIX: Default value for provider field;
* FIX: Capacity not calculated correctly after providers removed or added;
* FIX: Correct sanitize for some templates.

## 2.2.2
* FIX: WordPress Coding Standards errors and warnings.

## 2.2.1
* ADD: Dynamically changing the maximum capacity for apppointment list in calendar;
* FIX: Recurring incorrect set day offset by timezone;
* FIX: Set 0 in Price per slot field does not update the field `_app_price`;
* FIX: PHP fatal error with webhook;
* FIX: PHP Fatal error with woo integration.

## 2.2.0.1
* FIX: Compatibility with PHP versions < 8.1.0
  
## 2.2.0
* UPD: Optimize and refactor code;
* ADD: 2-way sync with Google Calendar;
* ADD: Appointment cancellation and confirmation deadline for Confirmation URLs;
* ADD: Ability to manage Appointment dashboard access from settings;
* ADD: Remove Timeslot Step in Booking Process When There Is Only 1 Timeslot;
* ADD: Errors for incorrectly filled or empty required fields in the appointment form;
* FIX: Slot Selection Doesn't Reset When Switching Services;
* FIX: Not all slots can be removed from the form;
* FIX: PHP warnings.

## 2.1.8
* FIX: Check date availability for excluded dates after editing working settings;
* FIX: No space between provider and service in Appointment details (Admin panel);
* FIX: Correct disabling excluded dates in the admin calendar;
* FIX: Min Slot Count settings does not work;
* FIX: Disabling multi booking when schedule type is not a slot.

## 2.1.7
* ADD: `Generate Same Confirmation URLs for appointments in group` option to make the same confirmation and cancellation links for all appointments in the group separately or single slot for all providers;
* FIX: Incorrect Capacity Calculation in Multi-Booking;
* FIX: Slot Becomes Unavailable in Multi-Booking Before Form Submission;
* FIX: Day Remains Excluded After Deleting Appointments;
* FIX: Slots unavailable from Admin panel without multi booking;
* FIX: Confirm grouped appointments after payment;
* FIX: Deprecated warnings.

## 2.1.6
* UPD: Search for the next available slot for the 12 months in future instead of 6;
* FIX: Admin appointments calendar: styles for RTL;
* FIX: Admin appointments calendar: issue with local timezone of the browser;
* FIX: Issue with styling of of WooCOmmerce Thank You Order widget from JetWooBuilder;
* FIX: Translations of some strings were unavailable;
* FIX: Problem with Days Off saving in some cases.

## 2.1.5
* ADD: `jet-apb/page/{$page_slug}/capability` filter to change access capability to Appointments admin page;
* ADD: `'jet-apb/capability` filter to change access capability to appropraite appointments action;
* ADD: `jet-apb/current-user-can` filter to add custom access validation to appropraite appointments action;
* ADD: `Service Slot Duplicating Across Providers` option to make the same slot available for each provider separately or single slot for all providers;
* UPD: Allow to override service custom schedule's Days Off with General Settings's Days Off;
* FIX: Prevent errors when user tries to book appointment, but not selected time in timepicker.

## 2.1.4
* UPD: Allow to change dates format in the apoointments list in the admin dashboard;
* FIX: Allow multi bookings from the admin dashboard;
* FIX: Update user local date and time after update appointment from the admin dashboard;
* FIX: Synch translation files;
* FIX: Next year appointments in the timepicke mode on mobile devices;
* FIX: Elementor Global styles on the cancellation page with custom template.

## 2.1.3
* ADD: Allow to set capacity for recurring appointments;
* ADD: Allow to send the same Zoom link for the same slot when capacity option is enabled;
* UPD: Set order by time for appointments in calendar view;
* UPD: Allow to change "Appointment time already taken" and "Appointment details" with custom labels;
* FIX: Preventtriggering workflow events for cancelled appointments;
* FIX: Send Email Workflow Duplicate Notifications in some cases.

## 2.1.2
* ADD: Appointments Group Created event for Workflows;
* UPD: Automatically switch to month with first available date in the Appointment calendar;
* FIX: Better JetFormBuilder compatibility;
* FIX: Avoid false triggering for CHange Appointment Status event;
* FIX: PHP warnings in some cases;
* FIX: Problem with default time in timepicker when current date selected.

## 2.1.1
* UPD: Synch calendar and providers when creating appointment from an admin area;
* FIX: In some cases clients can make a booking on already booked time;
* FIX: JFB Woo Addon compatibility. fatal error when Enable the updated checkout experience in Woo Stripe addon;
* FIX: Correctly save settings in setup wizard for some cases;
* FIX: Ensure `appointment_date` is always set;
* FIX: Deprecated warnings;
* FIX: Backend appointments not blocking on frontend.

## 2.1.0
* ADD: Allow to set booked slot capacity when Capacity option is enabled;
* ADD: Allow to set custom templates for Confirmation/Cancellation result page;
* UPD: Allow to set 0 price for service/provider.

## 2.0.7
* FIX: Better server-side check for appointment time availability;
* FIX: Attach Add Slot event to slots wrapper context to avoid unexpected behaviour;
* FIX: Calendar arrows direction in RTL;
* FIX: Show loader while getting new providers;
* FIX: Form error when using time range with only start time;
* FIX: Prevent PHP errors in some cases;
* FIX: Click on capacity doesn't select slot.

## 2.0.6
* UPD: Better security check on save appointments settings, setup and meta;
* FIX: Working days schedule processing;
* FIX: PHP errors in some cases;
* FIX: Prevent from booking 2 services slots for the same provider on same time when multi booking is enbaled;
* FIX: Display multiple appointments on the same day in JetEngine calendar;
* FIX: Additional server-side check on appointment slot availability before creating new appointmet.

## 2.0.5.1
* FIX: PHP error on macros handle after JetEngine update.

## 2.0.5
* ADD: Allow to group appoinments by days in the appointments list in admin area;
* FIX: Correctly process capacity setting when adding an appointment from the admin area;
* FIX: 'Add to Google calendar' link in WooCommerce order details;
* FIX: Date format option for macros in workflows;
* FIX: PHP error in custom provider template when there is no Elementor at website.

## 2.0.4
* UPD: Translation files;
* FIX: Limited range from current date option behavior;
* FIX: Correctly handle time limits on mobile timepicker;
* FIX: Avoid errors in appointments dashboard in some cases;
* FIX: Slot do not became available after cancelling appointment.

## 2.0.3
* UPD: Minor improvements in the Apoinmetns list UI/UX;
* FIX: Custom schedule overriding logic;
* FIX: Timepicker does not show already booked time.

## 2.0.2
* ADD: Allow to export appointments;
* ADD: Macros `Custom DB Column Value` to get the data from custom DB columns in Workflows;
* UPD: Compatibility with new WooCommerce REST API checkout;
* FIX: Minor Workflows UI bugs;
* FIX: Service meta macros inside Workflows.

## 2.0.1
* UPD: Allow to set user_name column value from front-end form;
* FIX: Avoid incorrect date calculations for some timezones;
* FIX: Duplicating form field for date selection in the JetPopup;
* FIX: Available slot calculation when system time not matching webiste timezone;
* FIX: Appointments table creation for some cases.

## 2.0.0
* ADD: Workflows functionality;
* ADD: Zoom integration;
* ADD: Timezones picker for the front-end appointments calendar UI;
* ADD: Public actions functionality (confirm and cancel appoinments by URL);
* ADD: Allow to override day schedule with Working Days settings;
* UPD: Admin UI improvements;
* FIX: SQL errors on MySQL 8.0 or higher;
* FIX: Allow to set 0 into service price.

## 1.6.10
* ADD: Allow to automatically change appointments status by Cron;
* ADD: Allow two-way synchronization for WC Orders and appointments;
* ADD: Allow to limit allowed for appointment days range;
* FIX: Adding appointments from admin area;
* FIX: Elemenotr Popup compatibility
* FIX: Providers ans Services switch when custom template is used.

## 1.6.9
* FIX: try to create DB tables only if not exists;
* FIX: days off checking;
* FIX: correctly process float values for slots;
* FIX: prevent PHP errors;
* FIX: correctly refresh services and providers list.

## 1.6.8
* ADD: Duration step in time picker settings

## 1.6.7
* FIX: Display appointments on the calendar
* FIX: Option "Availability check by"

## 1.6.6
* FIX: UTC time

## 1.6.5
* FIX: Choice of service

## 1.6.4
* FIX: Form style
* FIX: Choice of provider

## 1.6.3
* FIX: Loading form in ajax pop up
* ADD: Macro processing for Gutenberg. Example: \<!-- JFB_FIELD::date --\>

## 1.6.2
* FIX: Meta `_app_price`

## 1.6.1
* FIX: Manage Capacity

## 1.6.0
* ADD: Added new schedule type - Repeating appointment
* ADD: Added new schedule type - Time Picker appointment
* ADD: New appointment price settings for providers and services
* FIX: Fixed multi booking

## 1.5.8
* FIX: Dynamic Link - Add booking to Google calendar
* FIX: Appointment status if integration with woocommerce is enabled

## 1.5.7
* FIX: Date slots in admin panel

## 1.5.6
* FIX: Labels of statuses in the admin panel

## 1.5.5
* ADD: Compatibility with JetFormBuilder
* FIX: Saving global settings
* FIX: Empty days of weeks in the schedule
* FIX: The price from the calculator field is transferred to the WooCommerce prices
* FIX: Fixed a bug with booking different times with the same provider


## 1.5.1
* FIX: Time slots compatibility with php 7.1

## 1.5.0
* ADD: Multi booking
* ADD: Provider price
* UPD: Integration with woocommerce
* UPD: Notifications `Send Email` and `Call a Webhook`

## 1.4.0
* UPD: Admin panel for appointments
* ADD: Ability to edit and add appointments from the admin panel
* ADD: New views of appointments calendar and timeline.
* ADD: Filter for searching and sorting appointments in the admin panel

## 1.3.3
* FIX: Webhook date data

## 1.3.2
* FIX: Fixed appointment if option "Manage Capacity" is enabled

## 1.3.1
* FIX: Custom Schedule in services and providers

## 1.3.0
* ADD: Plugin settings have been moved to the Crocoblock dashboard
* ADD: Added the Slot Duration, Buffer Before Slot, Buffer After Slot of service and provider in the listing settings
* FIX: Display the name of service and provider in the admin panel if the service service or provider is private or with a password.

## 1.2.6
* ADD: New macros: `%service_link%` `%provider_link%` `%appointment_start%` `%appointment_end%`
* UPD: Timing control for options: Duration, Buffer Time Before Slot, Buffer Time After Slot
* UPD: If the date is fully booked, the `.jet-apb-calendar-date-disabled` class is added to it

## 1.2.5
* UPD: Change edit permissions

## 1.2.4
* FIX: Booking time error in WC details

## 1.2.3
* UPD: Added localization file

## 1.2.2
* FIX: WC product creation

## 1.2.1
* FIX: Saving custom schedule settings in services without a selected provider.

## 1.2.0
* ADD: Added the ability to select the period of working days and days off;
* ADD: Added Custom Schedule for single services and providers;
* ADD: Allow to add appointments details to WooCommerce orders;
* ADD: Added new macros for form email notification %service_title%, %provider_title%;
* ADD: Allow ability for users to add a appointment to their calendar;
* FIX: Fixed minor bugs.

## 1.1.1
* UPD: allow to correctly render appointment form on Ajax;
* UPD: allow to manage DB columns;
* FIX: disable next page button if time slot not selected in the calendar;
* FIX: providers REST API endpoint.

## 1.1.0
* ADD: Allow toi showcase appointments with Listing Grid  widget;
* ADD: Services capacity management;
* ADD: Allow to set custom labels for week days and months;
* ADD: Booking details to WooCommerce order e-mails;
* UPD: Allow to change time format in the calendar slots;
* UPD: Allow to use custom templates for providers select;
* UPD: Allow to correctly use radio field as services select;
* FIX: Appointment date format for e-mail;

## 1.0.0
* Initial release
