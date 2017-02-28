<?php

namespace Kauri\Loan\Test;


use Kauri\Loan\InterestAmountCalculator;
use Kauri\Loan\PaymentAmountCalculator;
use Kauri\Loan\PaymentPeriodsFactory;
use Kauri\Loan\PaymentsCalculator;
use Kauri\Loan\PaymentDateCalculator;
use Kauri\Loan\PaymentScheduleConfig;
use Kauri\Loan\PaymentScheduleFactory;
use Kauri\Loan\PeriodCalculator;
use PHPUnit\Framework\TestCase;

class PaymentsCalculatorTest extends TestCase
{
    /**
     * @dataProvider loanData
     * @param $noOfPayments
     * @param $principal
     * @param $interestRate
     * @param $expectedPaymentAmount
     */
    public function testScheduler($noOfPayments, $principal, $interestRate, $expectedPaymentAmount)
    {
        $paymentAmountCalculator = new PaymentAmountCalculator;
        $interestAmountCalculator = new InterestAmountCalculator;

        $config = new PaymentScheduleConfig($noOfPayments, new \DateTime(), 'P3D');

        $schedule = PaymentScheduleFactory::generate($config);

        $periods = PaymentPeriodsFactory::generate($schedule);

        $paymentsCalculator = new PaymentsCalculator(
            $periods,
            $paymentAmountCalculator,
            $interestAmountCalculator,
            $principal, $interestRate);

        $payments = $paymentsCalculator->getPayments();
        $firstPayment = current($payments);
        $this->assertEquals($expectedPaymentAmount, $firstPayment['payment']);
    }

    public function loanData()
    {
        return [
            [2, 2500, 0, 1250],
            [1, 1000, 360, 1030]
        ];
    }
}
