# 🏥 Appointment Booking System

A comprehensive clinic appointment booking system built with Laravel, implementing Domain-Driven Design (DDD) principles, SOLID principles, and modern design patterns.

## 🎯 System Features

### Core Functionality
- **Multi-Clinic Support**: Each clinic has multiple doctors
- **Doctor Time Slot Management**: Each doctor can define available time slots
- **Patient Booking**: Patients can search and book available slots
- **Exclusive Booking**: Each slot must be booked by only one patient
- **Dynamic Management**: New clinics and doctors can be added dynamically

### Architecture Highlights
- **Domain-Driven Design (DDD)** with clear domain boundaries
- **Service-Repository Pattern** for data access abstraction
- **Pipeline Pattern** for validation workflows
- **SOLID Principles** implementation throughout
- **RESTful API Design** with standardized responses

## 🏗️ Architecture Overview

### Domain Structure
```
app/Domains/
├── Clinic/         # Clinic management domain
├── Doctor/         # Doctor management domain
├── Patient/        # Patient management domain
├── Timeslot/       # Time slot management domain
└── Booking/        # Appointment booking domain
    ├── Contracts/      # Repository interfaces
    ├── Repositories/   # Data access implementations
    ├── Services/       # Business logic layer
    ├── Transformers/   # Response formatters
    ├── Pipelines/      # Validation
```

### Design Patterns Used

1. **Repository Pattern**: Data access abstraction
2. **Service Layer**: Business logic encapsulation
3. **Pipeline Pattern**: Validation chain processing
4. **Transformer**: Response formatting
5. **Dependency Injection**: Loose coupling

## 📊 Booking Operation Flow

```mermaid
sequenceDiagram
    participant Client
    participant BookingController
    participant BookingService
    participant ValidationPipeline
    participant BookingRepository
    participant Database

    Client->>BookingController: POST /bookings
    BookingController->>BookingService: bookAppointment()
    BookingService->>ValidationPipeline: process(context)
    
    ValidationPipeline->>ValidationPipeline: ValidateTimeSlotExists
    ValidationPipeline->>ValidationPipeline: ValidateTimeSlotAvailable
    ValidationPipeline->>ValidationPipeline: ValidateTimeSlotNotBooked
    ValidationPipeline->>ValidationPipeline: ValidateTimeSlotNotPast
    
    ValidationPipeline-->>BookingService: Validation passed
    BookingService->>BookingRepository: createAppointmentWithTimeSlotUpdate()
    
    BookingRepository->>Database: BEGIN TRANSACTION
    BookingRepository->>Database: UPDATE time_slots SET is_available = 0
    BookingRepository->>Database: INSERT INTO appointments
    BookingRepository->>Database: COMMIT TRANSACTION
    
    BookingRepository-->>BookingService: Appointment created
    BookingService-->>BookingController: Success response
    BookingController-->>Client: JSON response
```

## 🔒 Double Booking Prevention & Data Consistency

### 1. Database Level Constraints
```sql
-- Unique constraint on time_slot_id in appointments table
ALTER TABLE appointments ADD CONSTRAINT unique_timeslot UNIQUE (time_slot_id);
```

### 2. Transactional Integrity
```php
// Database transaction ensures atomicity
DB::transaction(function () use ($timeSlotId, $patientId) {
    $timeSlot = $this->timeslot->findOrFail($timeSlotId);
    $timeSlot->markAsUnavailable();
    $timeSlot->save();
    
    return $this->appointment->create([...]);
});
```

### 3. Pipeline Validation Chain
```php
// Multiple validation layers prevent conflicts
ValidateTimeSlotExists → 
ValidateTimeSlotAvailable → 
ValidateTimeSlotNotBooked → 
ValidateTimeSlotNotPast
```

### 4. Row-Level Locking
```php

$timeSlot = $this->timeslot->lockForUpdate()->findOrFail($timeSlotId);
```


## 🛠️ Installation & Setup

### Requirements
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- Composer

### Installation Steps
```bash
# Clone repository
git clone <repository-url>
cd appointment-booking-system

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Start development server
php artisan serve
```

### Database Schema
```bash
# Run migrations
php artisan migrate

# Tables created:
# - clinics
# - doctors
# - patients
# - time_slots
# - appointments
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```


## 📈 Scalability Features

- **Domain Separation**: Independent scaling of business domains
- **Repository Abstraction**: Easy database technology switching
- **Pipeline Architecture**: Extensible validation workflows
- **Stateless Design**: Horizontal scaling capabilities
- **API-First Approach**: Multi-client support

## 🔐 Security Features

- **Request Validation**: Comprehensive input validation
- **Database Transactions**: Data integrity protection
- **Unique Constraints**: Prevent duplicate bookings
- **Soft Deletes**: Data recovery capabilities
- **Error Handling**: Handles errors

## 🚀 Scaling for Enterprise Level

### Proposed Architecture for Thousands of Users & Clinics

```mermaid
graph LR
    LB[Load Balancer]
    
    subgraph "Application Servers"
        APP1[Laravel Instance 1]
        APP2[Laravel Instance 2] 
        APP3[Laravel Instance N]
    end
    
    REDIS[(Redis Cache<br/>Session & Data)]
    
    subgraph "Database"
        DB_MASTER[(MySQL Master<br/>Writes)]
        DB_SLAVE[(MySQL Slave<br/>Reads)]
    end
    
    S3[S3 Bucket<br/>Images & Files]
    
    LB --> APP1
    LB --> APP2
    LB --> APP3
    
    APP1 --> REDIS
    APP2 --> REDIS
    APP3 --> REDIS
    
    APP1 --> DB_MASTER
    APP2 --> DB_MASTER
    APP3 --> DB_MASTER
    
    APP1 --> DB_SLAVE
    APP2 --> DB_SLAVE
    APP3 --> DB_SLAVE
    
    DB_MASTER -.-> DB_SLAVE
    
    APP1 --> S3
    APP2 --> S3
    APP3 --> S3
```

