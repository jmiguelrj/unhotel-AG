<?php

namespace JFB\ScheduleForms\Vendor\JFBCore;

trait BlockEditorData
{
    public abstract function editor_data() : array;
    public abstract function editor_labels() : array;
    public abstract function editor_help() : array;
}
