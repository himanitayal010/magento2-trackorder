<?php

namespace Magneto\Trackorder\Controller\Order;

use Magento\Framework\Controller\ResultFactory;

class Shipment extends \Magento\Framework\App\Action\Action
{
  protected $resultPageFactory;

  protected $_orderCollectionFactory;

  protected $_order;

  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface, 
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_order = $order;
        parent::__construct($context);
    }
    public function execute()
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $statusCollection = $objectManager->get('Magneto\OrderUpdate\Model\ResourceModel\OrderUpdateStatus\Collection');
        $track = $this->getRequest()->getPost('track');
          if (!empty($track['order_id']) && !empty($track['customer_email'])) {

              $trackorder_id = $track['order_id'];

              $trackuser_email = $track['customer_email'];

              $order_detail = $this->_order->loadByIncrementId($trackorder_id);
              $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_detail['increment_id']);
              $orderItems = $order->getAllItems();
              $newIt = array();
              if($order_detail['customer_email'] == $trackuser_email){

                $order_method = $order_detail->getTracksCollection();  
                
                $trackData = array();
                $html = $orderifohtml = "";

                $orderifohtml = "<ul><li><span>". __('Order ID #') ."<b><p id='orderid'> ".$trackorder_id." </p></b></span></li></ul><table><tr><strong><th>Product Name</th><th>SKU</th><th>Qty</th><th>Status</th><th>Tracking</th></strong></tr>";
                $salesItemId = array();
                $trackNumber = 'Not Available';
                $status = 'Ordered';
                $model = $objectManager->get('\Magneto\OrderUpdate\Model\ResourceModel\OrderUpdate\Collection')->addFieldToFilter('order_id', $order_detail['entity_id']);
                $sales_track = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection')->addFieldToFilter('order_id', $order_detail['entity_id'])->addFieldToSelect('parent_id')->addFieldToSelect('track_number');
                $i = 0;
                foreach ($orderItems as $item) {
                  $itemId = $item->getItemId();
                  $newIt[] = $itemId;
                  if(in_array($itemId, $newIt)){
                    $i++;
                  }
                  // if($item->getParentItemId() == '' && $i <= 1){
                  if($item->getParentItemId() == ''){
                    if($sales_track->getSize()){
                    foreach ($sales_track as $track) {
                      $detauils = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Item\Collection')->addFieldToSelect('order_item_id')->addFieldToFilter('parent_id', $track->getParentId());
                      
                      foreach ($detauils as $trackvalue) {
                        $trackNumber = $track->getTrackNumber();
                        $trackLink = "<a href='https://www.delhivery.com/track/package/".$trackNumber."'> " . __('Click here') . " </a>";
                        foreach ($model as $values) {
                          if($values->getOrderItemId() == $trackvalue->getOrderItemId() && $values->getOrderItemId() == $itemId){
                            $statusId = $values->getOrderedItemStatusId();
                            $statusCollection->getSelect()->reset('where');
                            $statusData = clone $statusCollection;
                            $myStatus = $statusData->addFieldToFilter('ordered_item_status_id', $statusId);
                            foreach ($myStatus as $statusvalue) {
                                $status = $statusvalue->getOrderedItemStatusTitle();
                            }
                            $itemQty = (int)$values->getOrderedQty();
                            $salesItemId[] = $trackvalue->getOrderItemId();
                            if($statusId == '7' || $statusId == '5' || $statusId == '6' || $statusId == '10'){
                              $trackLink = 'Not Available';
                            }
                            $orderifohtml .= "<tr><td>".$item->getName()."</td><td>".$item->getSku()."</td><td>".$itemQty."</td><td>".$status."</td><td>".$trackLink."</td></tr>";
                          }
                        }
                        $i++;
                      }
                    }
                    }
                  }
                }
                if($salesItemId) {
                  foreach ($model as $value) {
                    if(in_array($value->getOrderItemId(), $salesItemId)){
                      // 
                    }else{
                      $statusId = $value->getOrderedItemStatusId();
                      $statusCollection->getSelect()->reset('where');
                      $statusData = clone $statusCollection;
                      $myStatus = $statusData->addFieldToFilter('ordered_item_status_id', $statusId);
                      foreach ($myStatus as $statusvalue) {
                        $status = $statusvalue->getOrderedItemStatusTitle();
                      }
                      $orderifohtml .= "<tr><td>".$value->getProductName()."</td><td>".$value->getProductSku()."</td><td>".(int)$value->getOrderedQty()."</td><td>".$status."</td><td>Not Available</td></tr>";
                    }
                  }
                }else{
                  foreach ($model as $otherIds) {
                    $statusId = $otherIds->getOrderedItemStatusId();
                      $statusCollection->getSelect()->reset('where');
                      $statusData = clone $statusCollection;
                      $myStatus = $statusData->addFieldToFilter('ordered_item_status_id', $statusId);
                      foreach ($myStatus as $statusvalue) {
                        $status = $statusvalue->getOrderedItemStatusTitle();
                      }
                    $orderifohtml .= "<tr><td>".$otherIds->getProductName()."</td><td>".$otherIds->getProductSku()."</td><td>".(int)$otherIds->getOrderedQty()."</td><td>".$status."</td><td>Not Available</td></tr>";
                  }
                }
                $orderifohtml .= "</table>";

                if(count($order_method)){
                  // foreach ($order_method as $trackingData) {
                  //   $html .="<tr><td><a href='https://www.delhivery.com/track/package/".$trackingData->getTrackNumber()."' target='_blank'>Track Order</a></td></tr>";   
                  // }
                  $success_message = __('We found a order detail #'.$order_detail['increment_id']);
                } else {
                  $success_message = __('Order #'. $order_detail['increment_id'] .' Not shipped yet');
                }
                $response  = array('trackData'=>$html, 'status' => true, 'emessage' => $success_message, 'orderdetailhtml' => $orderifohtml);                
              } else{
                  $error_message = __('No record found for the given details please check your Email or Order Id.');
                  $response =  array('status' => false, 'emessage' => $error_message);
              }

              $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

              $resultJson->setData($response);

              return $resultJson;

        }

    }

}
