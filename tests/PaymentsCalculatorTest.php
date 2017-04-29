<?php

namespace Kauri\Loan\Test;


use Kauri\Loan\InterestAmountCalculator;
use Kauri\Loan\PaymentAmountCalculator\AnnuityPaymentAmountCalculator;
use Kauri\Loan\PaymentAmountCalculator\EqualPrincipalPaymentAmountCalculator;
use Kauri\Loan\PaymentAmountCalculatorInterface;
use Kauri\Loan\PaymentPeriodsFactory;
use Kauri\Loan\PaymentsCalculator;
use Kauri\Loan\PaymentScheduleConfig;
use Kauri\Loan\PaymentScheduleFactory;
use Kauri\Loan\PeriodInterface;
use PHPUnit\Framework\TestCase;

class PaymentsCalculatorTest extends TestCase
{
    /**
     * @dataProvider dataLoader
     * @param $principal
     * @param $futureValue
     * @param $noOfPayments
     * @param $interestRate
     * @param $pattern
     * @param PaymentAmountCalculatorInterface $paymentAmountCalculator
     * @param $calculationMode
     * @param array $expectedPaymentAmounts
     */
    public function testCalculatePayments(
        $principal,
        $futureValue,
        $noOfPayments,
        $interestRate,
        $pattern,
        PaymentAmountCalculatorInterface $paymentAmountCalculator,
        $calculationMode,
        array $expectedPaymentAmounts
    ) {
        $interestAmountCalculator = new InterestAmountCalculator;

        $config = new PaymentScheduleConfig($noOfPayments, new \DateTime("2016-01-01"), $pattern);
        $schedule = PaymentScheduleFactory::generate($config);
        $periods = PaymentPeriodsFactory::generate($schedule);
        $paymentsCalculator = new PaymentsCalculator($paymentAmountCalculator, $interestAmountCalculator);

        $payments = $paymentsCalculator->calculatePayments($periods, $principal, $interestRate, $calculationMode,
            $futureValue);

        foreach ($payments as $k => $pmt) {
            $this->assertEquals($expectedPaymentAmounts[$k], $pmt['payment']);
        }
    }

    /**
     * @return array
     */
    public function dataLoader(): array
    {
        $interestAmountCalculator = new InterestAmountCalculator;

        $annuityPaymentAmountCalculator = new AnnuityPaymentAmountCalculator($interestAmountCalculator);
        $equalPaymentAmountCalculator = new EqualPrincipalPaymentAmountCalculator($interestAmountCalculator);

        $averageCalculationMode = PeriodInterface::LENGTH_MODE_AVG;
        $exactCalculationMode = PeriodInterface::LENGTH_MODE_EXACT;

        return [
            /* Annuity payments */
            // average interest
            [
                6000,
                0,
                5,
                360,
                'P1M',
                $annuityPaymentAmountCalculator,
                $averageCalculationMode,
                [1 => 2463.49, 2463.49, 2463.49, 2463.49, 2463.47]
            ],
            // exact interest
            [
                6000,
                0,
                5,
                360,
                'P1M',
                $annuityPaymentAmountCalculator,
                $exactCalculationMode,
                [1 => 2480.94, 2480.94, 2480.94, 2480.94, 2470.53]
            ],
            /* Equal principal payments */
            // average interest
            [
                6000,
                0,
                5,
                360,
                'P1M',
                $equalPaymentAmountCalculator,
                $averageCalculationMode,
                [1 => 3000, 2640, 2280, 1920, 1560]
            ],
            // average interest with future value
            [
                6000,
                2000,
                5,
                360,
                'P1M',
                $equalPaymentAmountCalculator,
                $averageCalculationMode,
                [1 => 2600, 2360, 2120, 1880, 1640]
            ],
            // exact payment
            [
                6000,
                0,
                5,
                360,
                'P1M',
                $equalPaymentAmountCalculator,
                $exactCalculationMode,
                [1 => 3060, 2592, 2316, 1920, 1572]
            ]
        ];
    }
}
