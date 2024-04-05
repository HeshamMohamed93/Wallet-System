# Wallet System

## Introduction
The wallet system is a digital platform designed to facilitate fund management for users. It allows users to deposit, transfer, and manage funds securely within their accounts.

## Getting Started
### Installation
- Clone the repository from GitHub.
- Install dependencies using Composer.
- Configure the environment variables.

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed



### Authentication

All endpoints require authentication using Sanctum Laravel authentication. Tokens are used in all requests to authenticate users.

### Usage
1. **Deposit Funds:**
    - Access the deposit feature in the user dashboard.
    - Enter the desired amount and confirm the transaction.

2. **Transfer Funds:**
    - Navigate to the transfer section.
    - Specify the recipient's email and the transfer amount.
    - Confirm the transfer with your PIN code.

3. **Change PIN Code:**
    - Visit the settings page.
    - Enter your old PIN code, followed by the new PIN code.
    - Confirm the new PIN code and authenticate with your password.

4. **Check Balance:**
    - View your current balance on the dashboard.
    - Authenticate with your PIN code to access the balance.

5. **Transaction History:**
    - Access the transaction history section.
    - Specify the desired date range within the maximum 3 months to view transactions.
    - Authenticate with your PIN code if required to access the history.

## API Documentation
The wallet system provides API endpoints for seamless integration with external applications. Below are the available endpoints:

## Deposit Funds

- **Endpoint:** `/api/wallet/deposit`
- **Method:** POST
- **Description:** Deposits funds into the user's wallet.
- **Request Parameters:**
    - `amount`: The amount to deposit.
- **Response Format:** JSON
- **Authentication Required:** Yes

## Check Wallet Balance

- **Endpoint:** `/api/wallet/balance`
- **Method:** POST
- **Description:** Retrieves the user's wallet balance.
- **Request Body:**
    - `pin_code`: User's PIN code for authentication.
- **Response Format:** JSON
- **Authentication Required:** Yes


## Transfer Funds

- **Endpoint:** `/api/wallet/transfer`
- **Method:** POST
- **Description:** Transfers funds to another user's wallet.
- **Request Parameters:**
    - `recipient_email`: Email of the recipient user.
    - `amount`: The amount to transfer.
    - `pin_code`: User's PIN code for authentication.
- **Response Format:** JSON
- **Authentication Required:** Yes

## Retrieve Transaction History

- **Endpoint:** `/api/wallet/transactions`
- **Method:** GET
- **Description:** Retrieves transaction history within a specified date range.
- **Request Parameters:**
    - `start_date`: Start date of the transaction history (format: `YYYY-MM-DD HH:MM:SS`).
    - `end_date`: End date of the transaction history (format: `YYYY-MM-DD HH:MM:SS`).
- **Response Format:** JSON
- **Authentication Required:** Yes

## Change PIN Code

- **Endpoint:** `/api/wallet/change-pin`
- **Method:** POST
- **Description:** Changes the user's wallet PIN code.
- **Request Body:**
    - `old_pin_code`: The current PIN code.
    - `new_pin_code`: The new PIN code.
    - `confirm_new_pin_code`: Confirmation of the new PIN code.
    - `password`: User's password for authentication.
- **Response Format:** JSON
- **Authentication Required:** Yes

## Troubleshooting
- If you encounter any issues with your transactions, ensure that your PIN code and recipient information are correct.
- For API-related problems, verify that you are using the correct endpoint and providing the necessary authentication credentials.

## Support
For any questions or assistance, please contact our developer, Hesham Mohamed, at [hesham.mohamed19930@gmail.com](mailto:hesham.mohamed19930@gmail.com).
