<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;

class Address
{
    /**
     * @var
     */
    private $companyname;

    /**
     * @var
     */
    private $firstname;

    /**
     * @var
     */
    private $lastname;

    /**
     * @var
     */
    private $streetaddress;

    /**
     * @var
     */
    private $zip;

    /**
     * @var
     */
    private $city;

    /**
     * @var
     */
    private $cellno;

    /**
     * @var
     */
    private $email;

    /**
     * @return mixed
     */
    public function getCompanyname()
    {
        return $this->companyname;
    }

    /**
     * @param mixed $companyname
     */
    public function setCompanyname($companyname): void
    {
        $this->companyname = $companyname;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getStreetaddress()
    {
        return $this->streetaddress;
    }

    /**
     * @param mixed $streetaddress
     */
    public function setStreetaddress($streetaddress): void
    {
        $this->streetaddress = $streetaddress;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCellno()
    {
        return $this->cellno;
    }

    /**
     * @param mixed $cellno
     */
    public function setCellno($cellno): void
    {
        $this->cellno = $cellno;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'companyname' => $this->companyname,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'streetaddress' => $this->streetaddress,
            'zip' => $this->zip,
            'city' => $this->city,
            'cellno' => $this->cellno,
            'email' => $this->email,
        ];
    }
}
