<?php
namespace App\Admin\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\Copyable;
use Dcat\Admin\Support\Helper;

class NewCopyable extends Copyable {

    public function display($displayText =null)
    {
        $this->addScript();

        $this->value = Helper::htmlEntityEncode($this->value);
        $displayText = $displayText ?? $this->value;

        $html = <<<HTML
<a href="javascript:void(0);" class="grid-column-copyable text-muted" data-content="{$this->value}" title="{$this->trans('copied')}" data-placement="bottom">
    <i class="fa fa-copy"></i>
    &nbsp;{$displayText}
</a>
HTML;

        return $this->value === '' || $this->value === null ? $this->value : $html;
    }
}
