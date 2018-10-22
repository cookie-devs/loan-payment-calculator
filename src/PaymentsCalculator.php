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
     * @param float $futureValue
     * @return array
     */
    public function calculatePayments(
        PaymentPeriodsInterface $paymentPeriods,
        float $amountOfPrincipal,
        float $yearlyInterestRate,
        int $calculationMode,
        float $futureValue
    ): array {
        $payments = array();

        $periodLengths = $this->getPeriodLengths($paymentPeriods, $calculationMode);
        $paymentAmounts = $this->paymentAmountCalculator->getPaymentAmounts($periodLengths, $amountOfPrincipal,
            $yearlyInterestRate, $futureValue);

        $principalLeft = $amountOfPrincipal;

        foreach ($paymentPeriods->getPeriods() as $period) {
            /**
             * Get payment amount
             */
            $paymentAmount = round($paymentAmounts[$period->getSequenceNo()], 2);

            /**
             * Get interest rate for period
             */
            $ratePerPeriod = $this->interestAmountCalculator->getPeriodInterestRate($yearlyInterestRate,
                $period->getLength($calculationMode));

            /**
             * Calculate interest part
             */
            $interest = round($this->interestAmountCalculator->getInterestAmount($principalLeft, $ratePerPeriod), 2);

            /**
             * Calculate principal part
             */
            if ($period->getSequenceNo() < $paymentPeriods->getNoOfPeriods()) {
                $principal = $paymentAmount - $interest;
            } else {
                $principal = $principalLeft - $futureValue;
            }

            /**
             * Calculate balance left
             */
            $principalLeft = $principalLeft - $principal;

            /**
             * Compose payment data
             */
            $paymentData = array(
                'sequence_no' => $period->getSequenceNo(),
                'payment' => $interest + $principal,
                'principal' => $principal,
                'interest' => $interest,
                'principal_left' => $principalLeft,
                'period' => $period
            );

            $payments[$period->getSequenceNo()] = $paymentData;
        }

        return $payments;
    }

    /**
     * @param PaymentPeriodsInterface $paymentPeriods
     * @param int $lengthMode
     * @return array
     */
    private function getPeriodLengths(PaymentPeriodsInterface $paymentPeriods, int $lengthMode): array
    {
        $lengths = array();

        /** @var PeriodInterface $period */
        foreach ($paymentPeriods->getPeriods() as $period) {
            $lengths[$period->getSequenceNo()] = $period->getLength($lengthMode);
        }

        return $lengths;
    }
}
