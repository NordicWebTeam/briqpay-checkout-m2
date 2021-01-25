<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote;

use Magento\Framework\Api\SearchCriteriaBuilder;

class QuoteRepository
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * QuoteManagement constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param $purchaseId
     */
    public function getByPurchaseId($purchaseId)
    {
        $this->searchCriteriaBuilder->addFilter(
            \Briqpay\Checkout\Setup\QuoteSchema::PURCHASE_ID,
            $purchaseId
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $list = $this->quoteRepository->getList($searchCriteria);

    }
}
