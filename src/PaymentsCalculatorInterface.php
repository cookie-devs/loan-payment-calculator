<?php

declare(strict_types = 1);

namespace Kauri\Loan;

/**
 * Interface PaymentsCalculatorInterface
 * @package Kauri\Loan
 */
interface PaymentsCalculatorInterface
{
    /**
     * PaymentsCalculatorInterface constructor.
     * @param PaymentAmountCalculatorInterface $paymentAmountCalculator
     * @param InterestAmountCalculatorInterface $interestAmountCalculator
     */
    public function __construct(
        PaymentAmountCalculatorInterface $paymentAmountCalculator,
        InterestAmountCalculatorInterface $interestAmountCalculator
    );

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
    ): array;

}
