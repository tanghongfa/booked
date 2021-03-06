<?php
/**
Copyright 2011-2014 Nick Korbel

This file is part of Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ROOT_DIR . 'lib/Email/Messages/ReservationEmailMessage.php');

class ParticipantAddedEmail extends ReservationEmailMessage
{
	/**
	 * @var User
	 */
	private $participant;

	public function __construct(User $reservationOwner, User $participant, ReservationSeries $reservationSeries, IAttributeRepository $attributeRepository)
	{
		parent::__construct($reservationOwner, $reservationSeries, $participant->Language(), $attributeRepository);

		$this->reservationOwner = $reservationOwner;
		$this->reservationSeries = $reservationSeries;
		$this->timezone = $participant->Timezone();
		$this->participant = $participant;
	}

	public function To()
	{
		$address = $this->participant->EmailAddress();
		$name = $this->participant->FullName();

		return array(new EmailAddress($address, $name));
	}

	public function Subject()
	{		
		/*
        * Add by Proteus Diamond Project -- Adding booking/reseravation title to email subject
        */
        $resourceTypeId = $this->reservationSeries->Resource()->GetResourceTypeId();
        $resourceTypeName = EmailResourceHelper::getInstance()->getResourceTypeName($resourceTypeId);
        return $this->Translate('ParticipantAddedSubject', array($resourceTypeName, $this->reservationSeries->Title()));
	}

	public function From()
	{
		return new EmailAddress($this->reservationOwner->EmailAddress(), $this->reservationOwner->FullName());
	}

    public function GetTemplateName()
    {
        return 'ReservationCreated.tpl';
    }
}