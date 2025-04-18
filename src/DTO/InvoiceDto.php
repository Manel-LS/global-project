<?php

namespace App\DTO;

use DateTime;

class InvoiceDto
{
    public string $nummvt;
    public string $libtrs;
    public string $codetrs;
    public DateTime $datemvt;
    public float $totalWithStamp;
    public float $totalPaid;
    public float $remainingAmount;
    public bool $paid;

    public function __construct(
        string $nummvt,
        string $libtrs,
        string $codetrs,
        DateTime $datemvt,
        float $mttc,
        float $timbref,
        float $mtrapp
    ) {
        $this->nummvt = $nummvt;
        $this->libtrs = $libtrs;
        $this->codetrs = $codetrs;
        $this->datemvt = $datemvt;
        $this->totalWithStamp = round($mttc, 3) + round($timbref, 3);
        $this->totalPaid = round($mtrapp, 3);
        $this->remainingAmount = round($this->totalWithStamp) - round($this->totalPaid);
        $this->paid = $this->remainingAmount == 0;
    }

    public function getNummvt(): string
    {
        return $this->nummvt;
    }

    public function getLibtrs(): string
    {
        return $this->libtrs;
    }



    public function getTotalWithStamp(): float
    {
        return $this->totalWithStamp;
    }

    public function getTotalPaid(): float
    {
        return $this->totalPaid;
    }

    public function getRemainingAmount(): float
    {
        return $this->remainingAmount;
    }

    public function getDatemvt()
    {
        return $this->datemvt;
    }

    public function getPaid()
    {
        return $this->paid;
    }


    public function getCodetrs()
    {
        return $this->codetrs;
    }
}
