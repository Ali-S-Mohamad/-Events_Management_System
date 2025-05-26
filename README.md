# Event Management System

A comprehensive Laravel-based event management platform that allows users to create, manage, and book events.

## Features

- **Event Management**: Create, edit, and delete events with detailed information
- **Location Tracking**: Associate events with specific locations including map coordinates
- **User Reservations**: Allow users to reserve spots for events
- **Role-Based Permissions**: Manage user access with Spatie's permission system
- **Image Management**: Upload and manage event and location images

## Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Ali-S-Mohamad/-Events_Management_System.git
cd -Events_Management_System
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=-Events_Management_System
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations and seed the database:
```bash
php artisan migrate --seed
```

6. Create link for storage disk
```bash
php artisan storage:link
```

7. Start the development server:
```bash
php artisan serve
```


## Model Relationships

- **User**
  - Has many `Reservations`
  - Has many `Events` 

- **Event**
  - Belongs to a `User`
  - Belongs to a `Location`
  - Belongs to an `EventType`
  - Has many `Reservations`
  - Has many `Images` (polymorphic)

- **Location**
  - Has many `Events`
  - Has many `Images` (polymorphic)

- **Reservation**
  - Belongs to a `User`
  - Belongs to an `Event`

## Accessors and Mutators

The application uses Laravel's Accessor and Mutator functionality to transform data:

- **Event**
  - `title`: Capitalizes words and trims whitespace
  - `formattedDate`: Formats event dates in a readable format
  - `duration`: Calculates human-readable duration

- **Location**
  - `name`: Capitalizes first letter and trims whitespace
  - `googleMapsUrl`: Generates Google Maps link from coordinates

- **Reservation**
  - `totalAttendees`: Calculates total number of attendees
  - `formattedCreatedAt`: Formats creation date



## Postman Collection

You can import the API endpoints into Postman using this collection:  
[Open Postman Collection]()
