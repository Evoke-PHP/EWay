<?php
/**
 * AccessCode
 *
 * @package   EWay\Response
 */

namespace EWay\Response;

/**
 * AccessCode
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package   EWay\Response
 */
class AccessCode extends Response
{
    /**
     * Get the access code from the response.
     *
     * @return string
     */
    public function getAccessCode()
    {
        return $this->get('AccessCode');
    }

    /**
     * Get the form action URL from the response.
     *
     * @return string
     */
    public function getFormActionURL()
    {
        return $this->get('FormActionURL');
    }
}
// EOF
