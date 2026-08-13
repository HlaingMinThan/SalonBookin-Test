# Salon Booking System — SPEC

## Overview
A simple web app where salon customers book available time slots,
and the salon admin manages services, slots, and approves/rejects bookings.

## Roles
- **Customer** — views open slots, books an appointment (no login required)
- **Admin** — manages services & slots, approves/rejects bookings (identified by `ADMIN_EMAIL` env var, uses existing Fortify auth)

## Features

### Customer
- View available time slots for a chosen service
- Book a slot by entering name + phone
- See confirmation page after booking
- Look up existing bookings by phone number

### Admin
- CRUD services (e.g. Haircut, Hair Color)
- Create time slots one by one (pick a service, date, time)
- View/delete slots
- View all bookings, approve or reject them

## Data

### Enum: BookingStatus
- `Pending`, `Approved`, `Rejected`, `Cancelled`

### Service
- `id`, `name`, `timestamps`
- Has many Slots

### Slot
- `id`, `service_id` (FK → services), `date`, `time`, `timestamps`
- Belongs to Service
- Has one Booking
- **Availability is derived** — a slot is available if it has no booking with Pending or Approved status (no `is_available` column)

### Booking
- `id`, `slot_id` (FK → slots), `customer_name`, `customer_phone`, `status` (default: Pending), `timestamps`
- Belongs to Slot
- Status cast to `BookingStatus` enum

## Rules
- One booking per slot — once booked (Pending/Approved), no longer available
- A booking must have a name and phone
- Bookings require admin approval (Pending → Approved/Rejected)

## Admin Auth
- `ADMIN_EMAIL` in `.env`, registered in `config/app.php`
- `User::isAdmin()` checks `$this->email === config('app.admin_email')`
- `EnsureAdmin` middleware protects all admin routes

## Routes

### Admin (`/admin/*`, auth + admin middleware)
| Method | URI | Action |
|--------|-----|--------|
| GET | /admin/services | List services |
| POST | /admin/services | Create service |
| PUT | /admin/services/{service} | Update service |
| DELETE | /admin/services/{service} | Delete service |
| GET | /admin/slots | List slots |
| POST | /admin/slots | Create slot |
| DELETE | /admin/slots/{slot} | Delete slot |
| GET | /admin/bookings | List bookings |
| PUT | /admin/bookings/{booking}/approve | Approve booking |
| PUT | /admin/bookings/{booking}/reject | Reject booking |

### Customer (public, no auth)
| Method | URI | Action |
|--------|-----|--------|
| GET | /book | Pick service & slot, fill name/phone |
| POST | /book | Submit booking |
| GET | /book/{booking} | Confirmation page |
| GET | /bookings | Enter phone number |
| POST | /bookings | Show bookings for phone |

## Vue Pages

### Admin (`resources/js/pages/Admin/`)
- `Services/Index.vue` — List + inline create/edit/delete
- `Slots/Index.vue` — List (grouped by date/service) + create form + delete
- `Bookings/Index.vue` — List with approve/reject buttons, status badges

### Customer (`resources/js/pages/`)
- `Book/Create.vue` — Pick service → see available slots → fill name/phone → submit
- `Book/Show.vue` — Confirmation page with booking details + status
- `Bookings/Index.vue` — Phone lookup form + results

## Build Order
1. Enum + Models + Migrations + Factories
2. Admin auth (User.isAdmin, config, EnsureAdmin middleware)
3. Admin Service CRUD (controller + routes + page + tests)
4. Admin Slot management (controller + routes + page + tests)
5. Customer booking flow (controller + routes + pages + tests)
6. Admin booking management (controller + routes + page + tests)
7. Customer booking lookup (controller + routes + page + tests)
8. Navigation updates (sidebar, Welcome page)
9. Seeder updates
10. Final test suite + pint formatting

## Tests
- **Admin/ServiceTest** — CRUD, non-admin access denied
- **Admin/SlotTest** — Create/delete, validation
- **Admin/BookingTest** — List, approve/reject
- **CustomerBookingTest** — View slots, create booking, slot unavailable after booking, confirmation
- **BookingLookupTest** — Lookup by phone
- **SlotAvailabilityTest** — `isAvailable()` and `available()` scope
