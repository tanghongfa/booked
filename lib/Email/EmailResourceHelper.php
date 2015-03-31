<?php
/**
This is helper class to get more information for Emails
*/

require_once(ROOT_DIR . 'Domain/Access/ResourceRepository.php');

class EmailResourceHelper
{
	private $resourceRepo;
	
    private static $_instance = null;

    public static function getInstance()
    {
        if (null === $_instance) {
            $_instance = new EmailResourceHelper();
        }

        return $_instance;
    }

    protected function __construct()
    {
        $this->resourceRepo = new ResourceRepository();
    }


    public function getResourceTypeName($resourceTypeId)
    {
        if(!empty($resourceTypeId)) {
            return $this->resourceRepo->LoadResourceType($resourceTypeId)->Name();
        } else {
            return "";
        }
    }

}
?>