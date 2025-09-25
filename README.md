# Crypto Wallet Application

A modern, responsive crypto wallet application built with PHP and MySQL, featuring user authentication, wallet management, and transaction tracking.

## Features

- **User Authentication**: Secure login and registration system
- **Multi-Currency Support**: Bitcoin (BTC), Ethereum (ETH), and Litecoin (LTC)
- **Wallet Management**: Create and manage multiple wallets
- **Transaction System**: Send and receive cryptocurrency (simulated)
- **Dashboard**: Real-time balance overview and transaction history
- **Responsive Design**: Mobile-friendly interface with Bootstrap 5
- **Security**: Password hashing, session management, and SQL injection prevention

## Screenshots

### Login Page
- Modern gradient design with form validation
- Demo account credentials provided
- Responsive layout

### Dashboard
- Multi-currency balance overview
- Wallet cards with send/receive functionality
- Real-time transaction history
- Clean, professional interface

## Installation

### Prerequisites
- XAMPP (or any PHP/MySQL environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone/Download** this project to your XAMPP `htdocs` directory
   ```
   C:\xampp\htdocs\crypto-wallet\
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import the `database.sql` file
   - Or run the SQL commands manually to create the database structure

4. **Configuration**
   - Edit `config/database.php` if your MySQL credentials are different
   - Default settings work with standard XAMPP installation

5. **Access the Application**
   - Visit: `http://localhost/crypto-wallet/`
   - Or run setup first: `http://localhost/crypto-wallet/setup.php`

## Demo Account

For quick testing, use the pre-created demo account:
- **Username**: `demo_user`
- **Password**: `password`

## File Structure

```
crypto-wallet/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── auth.php              # Authentication class
│   ├── session.php           # Session management
│   └── wallet.php            # Wallet management class
├── index.php                 # Main entry point
├── login.php                 # Login page
├── register.php              # Registration page
├── dashboard.php             # Main dashboard
├── logout.php                # Logout handler
├── setup.php                 # Setup instructions
├── database.sql              # Database structure
└── README.md                 # This file
```

## API Endpoints

The application includes AJAX endpoints for:

- **Create Wallet**: Create new cryptocurrency wallets
- **Send Transaction**: Send cryptocurrency between addresses
- **Simulate Receive**: Demo feature to add funds to wallets

## Database Schema

### Tables
- **users**: User accounts and authentication
- **wallets**: Cryptocurrency wallets for each user
- **transactions**: Transaction history and records
- **user_sessions**: Session management (future use)

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session management and CSRF protection
- Input validation and sanitization
- Secure logout with session destruction

## Customization

### Adding New Cryptocurrencies
1. Update the `generateWalletAddress()` method in `includes/wallet.php`
2. Add the new currency to the dropdown in `dashboard.php`
3. Add CSS styling for the currency icon

### Styling
- Modify the CSS in each PHP file
- Uses Bootstrap 5 and Font Awesome icons
- Gradient themes and modern UI elements

## Development Notes

### This is a Demo Application
- **Not for production use** - lacks many security features needed for real crypto
- Private keys stored in plain text (should be encrypted)
- No real blockchain integration
- Transactions are simulated in the database only

### Production Considerations
- Implement proper key encryption
- Add rate limiting and CAPTCHA
- Integrate with real cryptocurrency APIs
- Add email verification
- Implement 2FA authentication
- Add comprehensive logging
- Use HTTPS only

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Ensure the database exists

**Page Not Loading**
- Check if Apache is running
- Verify file permissions
- Check PHP error logs

**Login Issues**
- Use the demo account: `demo_user` / `password`
- Check if the users table has data
- Clear browser cookies/session

## License

This project is created for educational purposes. Feel free to modify and use as needed.

## Support

For issues or questions:
1. Check the setup instructions in `setup.php`
2. Verify your XAMPP configuration
3. Check PHP error logs for detailed error messages

## Future Enhancements

- QR code generation for wallet addresses
- Export transaction history
- Multiple language support
- Dark mode theme
- Mobile app version
- Real cryptocurrency integration
- Advanced security features
