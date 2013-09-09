<?php
###lit##

interface RuMage_OAuth_Interface
{
    /**
     * Return service name (id).
     * @abstract
     * @return mixed
     */
    public function getServiceName();

    /**
     * Return service title.
     * @abstract
     * @return mixed
     */
    public function getServiceTitle();

    /**
     * Return service type.
     * @abstract
     * @return mixed
     */
    public function getServiceType();

    /**
     * Sets redirect url after successful authorization.
     * @param string url to redirect.
     */
    public function setRedirectUrl($url);

    /**
     * Returns the redirect url after successful authorization.
     */
    public function getRedirectUrl();

    /**
     * Sets redirect url after unsuccessful authorization (e.g. user canceled).
     * @param string url to redirect.
     */
    public function setCancelUrl($url);

    /**
     * Returns the redirect url after unsuccessful authorization (e.g. user canceled).
     */
    public function getCancelUrl();

    /**
     * Authenticate the user.
     */
    public function authenticate();

    /**
     * Whether user was successfuly authenticated.
     */
    public function getIsAuthenticated();

    /**
     * Redirect to the url. If url is NULL, {@link redirectUrl} will be used.
     * @param string $url url to redirect.
     */
    public function redirect($url = NULL);

    /**
     * Redirect to the {@link cancelUrl} or simply close the popup window.
     */
    public function cancel();

    /**
     * Returns the user unique id.
     */
    public function getId();
}