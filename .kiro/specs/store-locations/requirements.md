# Requirements Document - Store Locations

## Introduction

El sistema de gestión de sedes permite a los administradores de tienda crear, gestionar y mostrar múltiples ubicaciones físicas de su negocio. Cada sede incluye información de contacto, horarios de atención flexibles, redes sociales y estado en tiempo real. El sistema respeta los límites del plan contratado y proporciona una experiencia completa tanto para administradores como para clientes finales.

## Requirements

### Requirement 1

**User Story:** As a store admin, I want to manage multiple store locations, so that I can provide accurate information about all my business locations to customers.

#### Acceptance Criteria

1. WHEN I access the locations section THEN the system SHALL display all my store locations with current status
2. WHEN I view the locations index THEN the system SHALL show my current usage vs plan limit (e.g., "Sedes: 3/5 - Plan Master")
3. WHEN I reach my plan limit THEN the system SHALL disable the "Create Location" button and show upgrade options
4. WHEN I have locations THEN the system SHALL display each location with name, city, status, phone, and manager
5. WHEN I view a location THEN the system SHALL show if it's currently open/closed based on current time and schedules

### Requirement 2

**User Story:** As a store admin, I want to create new store locations with complete information, so that customers can find and contact my business locations.

#### Acceptance Criteria

1. WHEN I create a location THEN the system SHALL require name, phone, department, city, and address
2. WHEN I create a location THEN the system SHALL allow optional fields: description, WhatsApp, manager name
3. WHEN I create a location THEN the system SHALL validate that location names are unique within my store
4. WHEN I create a location THEN the system SHALL validate phone number format
5. WHEN I create my first location THEN the system SHALL automatically set it as main location
6. WHEN I create a location THEN the system SHALL set it as active by default
7. WHEN I exceed my plan limit THEN the system SHALL prevent location creation and show error message

### Requirement 3

**User Story:** As a store admin, I want to configure flexible schedules for each location, so that customers know when each location is open.

#### Acceptance Criteria

1. WHEN I configure schedules THEN the system SHALL allow me to set different hours for each day of the week
2. WHEN I configure a day THEN the system SHALL allow me to mark it as closed
3. WHEN I configure a day THEN the system SHALL allow primary schedule (e.g., 8:00-18:00)
4. WHEN I configure a day THEN the system SHALL allow optional additional schedule (e.g., 14:00-18:00 for lunch break)
5. WHEN I set schedules THEN the system SHALL validate that opening time is before closing time
6. WHEN I set schedules THEN the system SHALL allow schedules that cross midnight (e.g., 22:00-02:00)
7. WHEN I save schedules THEN the system SHALL calculate current open/closed status automatically

### Requirement 4

**User Story:** As a store admin, I want to manage social media links for each location, so that customers can connect with specific locations on social platforms.

#### Acceptance Criteria

1. WHEN I configure social links THEN the system SHALL provide fixed platforms: Instagram, Facebook, TikTok, YouTube, WhatsApp, Link Linkiu
2. WHEN I add social links THEN the system SHALL validate URL format
3. WHEN I add social links THEN the system SHALL make all social links optional
4. WHEN I save social links THEN the system SHALL store them associated with the specific location
5. WHEN I view social links THEN the system SHALL display them with platform icons

### Requirement 5

**User Story:** As a store admin, I want to designate one location as the main location, so that customers can identify my primary business location.

#### Acceptance Criteria

1. WHEN I have multiple locations THEN the system SHALL allow only one main location at a time
2. WHEN I set a location as main THEN the system SHALL automatically remove main status from other locations
3. WHEN I deactivate the main location THEN the system SHALL prompt me to select a new main location from active locations
4. WHEN I have only one active location after deactivation THEN the system SHALL automatically set it as main
5. WHEN I view locations THEN the system SHALL clearly indicate which location is main with visual indicator

### Requirement 6

**User Story:** As a store admin, I want to activate/deactivate locations, so that I can control which locations are visible to customers.

#### Acceptance Criteria

1. WHEN I deactivate a location THEN the system SHALL hide it from public frontend
2. WHEN I deactivate a location THEN the system SHALL keep it visible in admin panel with inactive status
3. WHEN I activate a location THEN the system SHALL make it visible on public frontend
4. WHEN I deactivate the main location THEN the system SHALL require selecting a new main location
5. WHEN I view locations THEN the system SHALL show active/inactive status clearly

### Requirement 7

**User Story:** As a customer, I want to view store locations on the public website, so that I can find and contact the nearest location.

#### Acceptance Criteria

1. WHEN I visit the store frontend THEN the system SHALL display only active locations
2. WHEN I view a location THEN the system SHALL show name, address, phone, WhatsApp, and current status
3. WHEN I view a location THEN the system SHALL show today's schedule and current open/closed status
4. WHEN I view a location THEN the system SHALL show social media links with clickable icons
5. WHEN I see WhatsApp contact THEN the system SHALL provide direct WhatsApp link with predefined message
6. WHEN I view locations THEN the system SHALL clearly indicate which is the main location

### Requirement 8

**User Story:** As a store admin, I want to edit existing locations, so that I can keep location information up to date.

#### Acceptance Criteria

1. WHEN I edit a location THEN the system SHALL allow updating all location fields
2. WHEN I edit a location THEN the system SHALL maintain the same validation rules as creation
3. WHEN I edit a location name THEN the system SHALL validate uniqueness within my store
4. WHEN I edit schedules THEN the system SHALL recalculate current open/closed status
5. WHEN I save changes THEN the system SHALL update the location and show success message

### Requirement 9

**User Story:** As a store admin, I want to delete locations I no longer need, so that I can keep my location list clean and accurate.

#### Acceptance Criteria

1. WHEN I delete a location THEN the system SHALL show confirmation dialog with location name
2. WHEN I delete a non-main location THEN the system SHALL allow deletion immediately
3. WHEN I delete the main location THEN the system SHALL require selecting a new main location first
4. WHEN I confirm deletion THEN the system SHALL permanently remove the location and all associated data
5. WHEN I delete a location THEN the system SHALL update my plan usage count

### Requirement 10

**User Story:** As a store admin, I want to track WhatsApp interactions, so that I can measure customer engagement with my locations.

#### Acceptance Criteria

1. WHEN a customer clicks WhatsApp link THEN the system SHALL increment click counter for that location
2. WHEN I view location details THEN the system SHALL show WhatsApp click statistics
3. WHEN I view locations index THEN the system SHALL show basic engagement metrics
4. WHEN I access analytics THEN the system SHALL provide WhatsApp click data per location
5. WHEN tracking clicks THEN the system SHALL not store personal customer information