[![Build Status](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kaurikk/loan-payment-calculator/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/53e4aec7-ba0e-4099-b6ae-9565d8cd1045/mini.png)](https://insight.sensiolabs.com/projects/53e4aec7-ba0e-4099-b6ae-9565d8cd1045)


# loan-payment-calculator
Library to calculate full loan payments (with dates, periods, principal and interest amounts).

## Basic usage

```php
// $paymentAmountCalculator is insance of PaymentAmountCalculatorInterface
// $interestAmountCalculator is instance of InterestAmountCalculatorInterface
$calculator = new PaymentsCalculator($paymentAmountCalculator, $interestAmountCalculator);

$periods = $paymentPeriods; // must be instance of PaymentPeriodsInterface
$principal = 2000;
$interestRate = 20;
$calculationMode = 1 // see PaymentPeriodsInterface for available modes

$payments = $calculator->calculatePayments($periods, $principal, $interestRate, $calculationMode);
```
