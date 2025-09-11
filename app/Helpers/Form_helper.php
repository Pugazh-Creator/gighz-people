<?php
    // dispaly error from after validation 

    function dispaly_form_error($validation,$field)
    {
        if($validation->hasError($field))
        {
            return $validation->getError($field);
        }
    }