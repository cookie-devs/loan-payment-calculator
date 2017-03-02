<?php

declare(strict_types = 1);

namespace Kauri\Loan;


class PaymentsCalculator implements PaymentsCalculatorInterface
{
    /**
     * @var PaymentAmountCalculatorInterface
     */
    private $paymentAmountCalculator;
    /**
     * @var InterestAmountCalculatorInterface
     */
    private $interestAmountCalculator;

    /**
     * PaymentsCalculator constructor.
     * @param PaymentAmountCalculatorInterface $paymentAmountCalculator
     * @param InterestAmountCalculatorInterface $interestAmountCalculator
     */
    public function __construct(
        PaymentAmountCalculatorInterface $paymentAmountCalculator,
        InterestAmountCalculatorInterface $interestAmountCalculator
    ) {
        $this->paymentAmountCalculator = $paymentAmountCalculator;
        $this->interestAmountCalculator = $interestAmountCalculator;
    }

    /**
     * @param PaymentPeriodsInterface $paymentPeriods
     * @param float $amountOfPrincipal
     * @param float $yearlyInterestRate
     * @param int $calculationMode
     * @return array
     */
    public function getPayments(
        PaymentPeriodsInterface $paymentPeriods,
        float $amountOfPrincipal,
        float $yearlyInterestRate,
        int $calculationMode
    ): array {
        $payments = array();

        $numberOfPayments = $paymentPeriods->getNoOfPeriods();
        $principalLeft = $amountOfPrincipal;

        foreach ($paymentPeriods->getPeriods() as $key => $period) {
            $ratePerPeriod = $paymentPeriods->getRatePerPeriod($period, $yearlyInterestRate, $calculationMode);
            $numberOfPeriods = $paymentPeriods->getNumberOfRemainingPeriods($period, $calculationMode);

            /**
             * Calculate payment amount
             */
            $paymentAmount = $this->paymentAmountCalculator->getPaymentAmount($principalLeft, $ratePerPeriod,
                $numberOfPeriods);

            /**
             * Calculate interest part
             */
            $interest = $this->interestAmountCalculator->getInterestAmount($principalLeft, $ratePerPeriod);

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

            $payments[$key] = $paymentData;
        }

        return $payments;
    }
}
