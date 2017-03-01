<?php

declare(strict_types = 1);

namespace Kauri\Loan;


class PaymentsCalculator implements PaymentsCalculatorInterface
{
    /**
     * @var array
     */
    private $payments = array();

    /**
     * PaymentsCalculator constructor.
     * @param PaymentPeriodsInterface $paymentPeriods
     * @param PaymentAmountCalculatorInterface $paymentAmountCalculator
     * @param InterestAmountCalculatorInterface $interestAmountCalculator
     * @param float|int $amountOfPrincipal
     * @param float|int $yearlyInterestRate interest rate for 360 days
     */
    public function __construct(
        PaymentPeriodsInterface $paymentPeriods,
        PaymentAmountCalculatorInterface $paymentAmountCalculator,
        InterestAmountCalculatorInterface $interestAmountCalculator,
        float $amountOfPrincipal,
        float $yearlyInterestRate
    ) {
        $numberOfPayments = $paymentPeriods->getNoOfPeriods();

        $principalLeft = $amountOfPrincipal;
        $calculationType = $paymentPeriods::CALCULATION_TYPE_ANNUITY;

        foreach ($paymentPeriods->getPeriods() as $key => $period) {
            $ratePerPeriod = $paymentPeriods->getRatePerPeriod($period, $yearlyInterestRate, $calculationType);
            $numberOfPeriods = $paymentPeriods->getNumberOfRemainingPeriods($period, $calculationType);

            /**
             * Calculate payment amount
             */
            $paymentAmount = $paymentAmountCalculator->getPaymentAmount($principalLeft, $ratePerPeriod,
                $numberOfPeriods);

            /**
             * Calculate interest part
             */
            $interest = $interestAmountCalculator->getInterestAmount($principalLeft, $ratePerPeriod);

            /**
             * Calculate principal part
             */
            if ($key < $numberOfPayments) {
                $principal = $paymentAmount - $interest;
            } else {
                $principal = $principalLeft;
            }

            /**
             * Calculate balance left
             */
            $principalLeft = round($principalLeft - $principal, 2);

            /**
             * Compose payment data
             */
            $paymentData = array(
                'payment' => $interest + $principal,
                'principal' => $principal,
                'interest' => $interest,
                'principal_left' => $principalLeft,
                'period' => $period
            );

            $this->payments[$key] = $paymentData;
        }
    }

    /**
     * @return array
     */
    public function getPayments(): array
    {
        return $this->payments;
    }
}
