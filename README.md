# Buea BloodLink

## Overview
Buea BloodLink is a web-based Blood Donation Management System designed to facilitate blood donation and requests. The application connects donors with hospitals and individuals in need of blood, ensuring a streamlined process for blood donation and emergency requests.

## Features
- User authentication (login and registration)
- Search functionality for blood types and locations
- Booking appointments for blood donation
- Requesting blood units from hospitals
- Responsive design for mobile and desktop users
- Modular CSS for reusable components
- Mock API for testing and development

## Project Structure
```
buea-bloodlink-frontend
├── index.html          # Home page of the application
├── css
│   ├── style.css      # Main styles for layout and typography
│   └── components.css  # Modular styles for specific components
├── js
│   ├── app.js         # Main JavaScript file for initialization
│   ├── api.js         # Functions for API calls
│   ├── auth.js        # Authentication-related functionality
│   ├── ui.js          # User interface interactions
│   └── utils.js       # Utility functions
├── mock
│   └── api
│       └── mock-data.json  # Dummy JSON data for testing
├── tests
│   └── ui.test.js     # Unit tests for UI components
├── package.json       # npm configuration file
├── .gitignore         # Files and directories to ignore by Git
└── README.md          # Project documentation
```

## Setup Instructions
1. Install XAMPP from https://www.apachefriends.org/ (includes Apache, MySQL, and PHP).
2. Start XAMPP and ensure Apache and MySQL are running (green status).
3. Copy or move the project folder to `C:\xampp\htdocs\buea-bloodlink-frontend`.
4. Open phpMyAdmin at http://localhost/phpmyadmin/.
5. Create a new database named `buea_bloodlink`.
6. Import `db_setup.sql` into the database.
7. Access the app at http://localhost/buea-bloodlink-frontend/index.html.
8. For development, install Node.js dependencies if needed:
   ```
   npm install
   ```

## Usage
- Users can register and log in to access the features of the application.
- Donors can search for blood types and book appointments for donation.
- Hospitals can request blood units as needed.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any suggestions or improvements.

## Database Setup
1. Import the `db_setup.sql` file into MySQL:
   ```sql
   SOURCE db_setup.sql;
   ```
2. Configure `php/config.php` database credentials.
3. The app auto-creates all required tables and an admin user (admin@bueabloodlink.local / admin123).

## New App Pages
- `search_donors.php`: search donors by blood group/location + result cards.
- `request.php`: submit blood requests + view requests.
- `bloodbank_dashboard.php`: review and process requests (approve/reject).

## License
This project is licensed under the MIT License. See the LICENSE file for details.