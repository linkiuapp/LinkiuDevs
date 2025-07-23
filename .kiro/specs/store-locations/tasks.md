# Implementation Plan - Store Locations

## Task Overview

This implementation plan creates a comprehensive store locations management system for the TenantAdmin feature, following the established UI patterns from SuperAdmin stores management. The system will handle CRUD operations, flexible scheduling, social media integration, and plan-based limitations while maintaining visual consistency with existing components.

## Implementation Tasks

- [ ] 1. Database Foundation








  - Create migration for store_locations table with all required fields
  - Create migration for location_schedules table with flexible time slots
  - Create migration for location_social_links table with platform constraints
  - Add max_locations field to plans table or configuration
  - _Requirements: 1, 2, 3, 8_

- [x] 2. Core Models and Relationships



  - Create Location model in App\Shared\Models with BelongsToTenant trait
  - Create LocationSchedule model with time validation methods
  - Create LocationSocialLink model with platform validation
  - Implement model relationships and business logic methods
  - Add location relationship to Store model


  - _Requirements: 1, 2, 3, 4, 8_

- [ ] 3. Location Service Layer
  - Create LocationService in App\Features\TenantAdmin\Services
  - Implement plan limit validation methods
  - Implement schedule creation and validation logic


  - Implement main location management logic
  - Implement current status calculation methods
  - _Requirements: 1, 5, 6, 8_

- [ ] 4. Location Controller with CRUD Operations
  - Create LocationController in App\Features\TenantAdmin\Controllers
  - Implement index method with filtering and plan limit display
  - Implement create method with plan limit validation


  - Implement store method with schedule and social links creation
  - Implement show method with status calculation
  - Implement edit and update methods
  - Implement destroy method with main location handling
  - _Requirements: 1, 2, 7, 8, 9_



- [ ] 5. Routes and Middleware Integration
  - Add location routes to TenantAdmin routes file
  - Implement route model binding for Location
  - Add tenant identification middleware
  - Add plan limit validation middleware
  - Test route accessibility and parameter binding
  - _Requirements: 1, 8_



- [ ] 6. Location Index View (Following Stores Pattern)
  - Create index.blade.php with same structure as stores index
  - Implement header component with plan limit indicator
  - Create table-view component with location data display
  - Implement status indicators (open/closed) with real-time calculation
  - Add action buttons (view, edit, delete) with same styling

  - Implement search and filter functionality
  - _Requirements: 1, 5, 6_

- [ ] 7. Location Create Form (Following Stores Pattern)
  - Create create.blade.php with same card structure as stores
  - Implement basic information section (name, description, manager, contact)
  - Implement address section (department, city, address)
  - Implement main location toggle with validation

  - Add form validation with error display patterns
  - Implement plan limit check and disable creation if needed
  - _Requirements: 2, 8_

- [ ] 8. Schedule Management Component
  - Create schedule form component for weekly schedule grid
  - Implement day-by-day schedule configuration


  - Add primary and additional time slot inputs
  - Implement closed day toggle functionality
  - Add time validation (opening < closing time)
  - Handle midnight-crossing schedules
  - _Requirements: 3_

- [ ] 9. Social Media Links Component



  - Create social links form component with fixed platforms
  - Implement URL validation for each platform
  - Add platform icons and visual indicators
  - Implement optional WhatsApp message configuration
  - Add URL format validation with error messages
  - _Requirements: 4_

- [ ] 10. Location Edit Form (Following Stores Pattern)
  - Create edit.blade.php with same structure as stores edit
  - Pre-populate all form fields with existing data
  - Implement schedule editing with current values
  - Implement social links editing with current values
  - Add main location change functionality
  - Maintain same footer button layout as stores
  - _Requirements: 2, 3, 4, 5, 8_

- [ ] 11. Location Show/Details View (Following Stores Pattern)
  - Create show.blade.php with same layout as stores show
  - Display location information in organized sections
  - Show current status with real-time calculation
  - Display schedule information in readable format
  - Show social media links with clickable icons
  - Add WhatsApp click tracking functionality
  - Implement action buttons (edit, delete) with same styling
  - _Requirements: 6, 10_

- [ ] 12. Status Toggle and Main Location Management
  - Implement toggleStatus method in controller
  - Create AJAX endpoint for status changes
  - Implement setAsMain method with validation
  - Handle main location deactivation with modal selection
  - Add JavaScript for real-time status updates
  - Implement confirmation dialogs for critical actions
  - _Requirements: 5, 6_

- [ ] 13. Delete Modal and Confirmation (Following Stores Pattern)
  - Create delete-modal component with same structure as stores
  - Implement confirmation dialog with location name display
  - Handle main location deletion with validation
  - Add AJAX delete functionality
  - Implement success/error notifications
  - _Requirements: 9_

- [ ] 14. Notification System Integration
  - Create notifications component following stores pattern
  - Implement toast notifications for success/error states
  - Add notification triggers for all CRUD operations
  - Implement auto-hide functionality
  - Add notification for plan limit warnings
  - _Requirements: 1, 8_

- [ ] 15. Plan Limit Enforcement
  - Implement plan limit validation in controller
  - Add visual indicators for current usage vs limits
  - Disable create button when limit reached
  - Show upgrade prompts when appropriate
  - Add limit validation to all creation endpoints
  - _Requirements: 1, 8_

- [ ] 16. Real-time Status Calculation
  - Implement getCurrentStatus method in Location model
  - Add status calculation based on current time and schedules
  - Handle different status states (open, closed, temporarily closed)
  - Implement next status change calculation
  - Add status display in all location views
  - _Requirements: 6_

- [ ] 17. WhatsApp Integration and Click Tracking
  - Implement WhatsApp link generation with custom messages
  - Add click tracking functionality for analytics
  - Create incrementWhatsAppClicks method
  - Add click counter display in location details
  - Implement WhatsApp button with proper formatting
  - _Requirements: 4, 10_

- [ ] 18. Form Validation and Error Handling
  - Implement comprehensive form validation rules
  - Add client-side validation for immediate feedback
  - Implement server-side validation with proper error messages
  - Add unique name validation per store
  - Implement phone number format validation
  - Add URL validation for social media links
  - _Requirements: 8_

- [ ] 19. JavaScript Component (Following Standards)
  - Create locationManagement Alpine.js component
  - Implement CRUD operations with AJAX
  - Add form validation and error handling
  - Implement modal management (show/hide)
  - Add notification system integration
  - Follow established JavaScript standards from project
  - _Requirements: All_

- [ ] 20. Testing and Quality Assurance
  - Create unit tests for Location model methods
  - Create feature tests for LocationController
  - Test plan limit enforcement scenarios
  - Test main location management edge cases
  - Test schedule validation and status calculation
  - Verify UI consistency with stores management
  - Test responsive design and mobile compatibility
  - _Requirements: All_

## Implementation Notes

### UI Consistency Requirements
- All views must follow the exact same structure as SuperAdmin stores
- Use identical CSS classes and component patterns
- Maintain same modal, notification, and form styling
- Follow established color scheme and typography
- Use Solar Icons consistently throughout

### Technical Requirements
- Follow feature-based architecture pattern
- Implement proper tenant isolation
- Use established service layer pattern
- Maintain database relationship integrity
- Follow project's JavaScript standards

### Validation Requirements
- Enforce plan limits at all entry points
- Validate schedule logic and time formats
- Ensure main location business rules
- Validate social media URL formats
- Implement proper error messaging

### Performance Considerations
- Implement eager loading for relationships
- Cache status calculations where appropriate
- Optimize database queries with proper indexes
- Use AJAX for real-time updates without page refresh

## Success Criteria

Upon completion, the store locations system should:
- Provide complete CRUD functionality matching stores management UI
- Enforce plan limitations accurately
- Calculate real-time open/closed status
- Handle complex scheduling scenarios
- Manage main location designation properly
- Track WhatsApp interactions for analytics
- Maintain visual consistency with existing admin interface
- Pass all validation and business rule requirements