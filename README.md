# Commision Fee Calculator

This application reads operations (withdraws and deposits) from a CSV file and outputs the calculated commission fee for each transaction (one per line). The currency conversion rates and commission rules are defined as follows:

- **Deposit:** Fee is 0.03% of deposit amount.
- **Withdraw – Business:** Fee is 0.5% of withdraw amount.
- **Withdraw – Private:**  
  - Fee is 0.3% of the amount exceeding a per‑week (Monday-to‑Sunday) free limit of 1000.00 EUR that applies only for the first 3 withdraw operations.
  - If the operation’s currency is not EUR, the free limit is converted (using a configurable conversion rate), then the fee is calculated on the exceeded amount.  
  - For withdraw operations beyond the third in a week, the full amount is charged the fee.

## How to run the application

Use the command below (assuming your CSV file is named `input.csv`):

```bash
php script.php input.csv

How to run the test

Use the command below:
```#bash
php tests/test.php
