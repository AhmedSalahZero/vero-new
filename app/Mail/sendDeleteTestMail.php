<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendDeleteTestMail extends Mailable
{
	use Queueable, SerializesModels;


	public function __construct()
	{
	}

	public function build()
	{
		return $this->subject('Test Mail')->from(env('EMAIL_FROM'), env('APP_NAME'))->view('mails.test-delete');
	}
}
