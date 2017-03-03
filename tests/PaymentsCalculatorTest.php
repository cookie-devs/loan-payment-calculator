<?php

namespace Kauri\Loan\Test;


use Kauri\Loan\InterestAmountCalculator;
use Kauri\Loan\PaymentAmountCalculator\AnnuityPaymentAmountCalculator;
use Kauri\Loan\PaymentAmountCalculator\EqualPrincipalPaymentAmountCalculator;
use Kauri\Loan\PaymentAmountCalculatorInterface;
use Kauri\Loan\PaymentPeriodsFactory;
use Kauri\Loan\PaymentPeriodsInterface;
use Kauri\Loan\PaymentsCalculator;
use Kauri\Loan\PaymentScheduleConfig;
use Kauri\Loan\PaymentScheduleFactory;
use PHPUnit\Framework\TestCase;

class PaymentsCalculatorTest extends TestCase
{
    /**
     * @dataProvider loanData
     * @param int $noOfPayments
     * @param int $principal
     * @param int $interestRate
     * @param float $expectedPaymentAmount
     * @param PaymentAmountCalculatorInterface $paymentAmountCalculator
     */
    public function testCalculatePayments(
        $noOfPayments,
        $principal,
        $interestRate,
        $expectedPaymentAmount,
        PaymentAmountCalculatorInterface $paymentAmountCalculator
    ) {
        $interestAmountCalculator = new InterestAmountCalculator;

        $config = new PaymentScheduleConfig($noOfPayments, new \DateTime(), 'P3D');
        $schedule = PaymentScheduleFactory::generate($config);
        $periods = PaymentPeriodsFactory::generate($schedule);
        $paymentsCalculator = new PaymentsCalculator($paymentAmountCalculator, $interestAmountCalculator);

        $calculationMode = PaymentPeriodsInterface::CALCULATION_MODE_AVERAGE;

        $payments = $paymentsCalculator->calculatePayments($periods, $principal, $interestRate, $calculationMode);
        $firstPayment = current($payments);
        $this->assertEquals($expectedPaymentAmount, $firstPayment['payment']);
    }

    /**
     * @return array
     */
    public function loanData(): array
    {
        $annuityPaymentAmountCalculator = new AnnuityPaymentAmountCalculator();
        $equalPaymentAmountCalculator = new EqualPrincipalPaymentAmountCalculator();

        return [
            [2, 2500, 0, 1250, $annuityPaymentAmountCalculator],
            [1, 1000, 360, 1030, $annuityPaymentAmountCalculator],
            [3, 3000, 0, 1000, $equalPaymentAmountCalculator],
            [3, 110, 0, 36.67, $equalPaymentAmountCalculator],
            [3, 4000, 0, 1333.33, $equalPaymentAmountCalculator],
            [3, 3000, 360, 1090, $equalPaymentAmountCalculator]
        ];
    }
}
