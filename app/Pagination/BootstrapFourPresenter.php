<?php
namespace App\Pagination;

use Illuminate\Pagination\BootstrapThreePresenter;

class BootstrapFourPresenter extends BootstrapThreePresenter
{
    /**
     * Convert the URL window into Bootstrap HTML.
     *
     * @return string
     */
    public function render()
    {
        if ($this->hasPages()) {
            return sprintf(
                '<ul class="pagination float-right">%s %s %s</ul>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }

        return '';
    }

    /**
     * Get HTML wrapper for an available page link.
     *
     * @param  string  $url
     * @param  int  $page
     * @param  string|null  $rel
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page, $rel = null)
    {
        $rel = is_null($rel) ? '' : ' rel="' . $rel . '"';

        return '<li class="page-item"><a class="page-link" href="' . htmlentities($url) . '"' . $rel . '>' . $page . '</a></li>';
    }

    /**
     * Get HTML wrapper for disabled text.
     *
     * @param  string  $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="page-item disabled"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * Get HTML wrapper for active text.
     *
     * @param  string  $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="page-item active"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * Get a pagination "dot" element.
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper("...");
    }

    /**
     * Get the previous page pagination element.
     *
     * @param  string  $text
     * @return string
     */
    // protected function getPreviousButton($text = 'Previous')
    // {
    //     return parent::getPreviousButton($text);
    // }

    /**
     * Get the next page pagination element.
     *
     * @param  string  $text
     * @return string
     */
    // protected function getNextButton($text = 'Next')
    // {
    //     return parent::getNextButton($text);
    // }
}
