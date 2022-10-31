<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class DataFreeze extends Mailable{
    
    protected $data;

    public function __construct($data){
        
        $this->data = $data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){

        return $this->subject('Congelamiento de Indicadores ' . $this->data->date)
                    ->markdown('emails.data_freeze')
                    ->with([
                        'date' => $this->data->date,
                        'start_time' => $this->data->start_time,
                        'finish_time' => $this->data->finish_time
                    ]);
    
    }
}
