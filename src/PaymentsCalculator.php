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
    public function calculatePayments(
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
            $paymentAmount = round($this->paymentAmountCalculator->getPaymentAmount($principalLeft, $ratePerPeriod,
                $numberOfPeriods), 2);

            /**
             * Calculate interest part
             */
            $interest = round($this->interestAmountCalculator->getInterestAmount($principalLeft, $ratePerPeriod), 2);

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
            $principalLeft = $principalLeft - $principal;

            /**
             * Compose payment data
             */
            $paymentData = array(
                'sequence_no' => $key,
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
