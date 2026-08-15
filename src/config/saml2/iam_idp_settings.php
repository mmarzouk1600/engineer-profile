<?php

// If you choose to use ENV vars to define these values, give this IdP its own env var names
// so you can define different values for each IdP, all starting with 'SAML2_'.$this_idp_env_id
$this_idp_env_id = 'IAM';

//This is variable is for simplesaml example only.
// For real IdP, you must set the url values in the 'idp' config to conform to the IdP's real urls.
$idp_host = env('SAML2_'.$this_idp_env_id.'_IDP_HOST', '');
return $settings = array(

    /*****
     * One Login Settings
     */

    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Authority, NameId, Conditions ... are validated too.
    'strict' => true, //@todo: make this depend on laravel config

    // Enable debug mode (to print errors)
    'debug' => env('APP_DEBUG', true),

    // Service Provider Data that we are deploying
    'sp' => array(

        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => env('SAML2_'.$this_idp_env_id.'_SP_x509',''),
        'privateKey' => env('SAML2_'.$this_idp_env_id.'_SP_PRIVATEKEY',''),

        // Identifier (URI) of the SP entity.
        // Leave blank to use the '{idpName}_metadata' route, e.g. 'test_metadata'.
        'entityId' => 'https://in.mu.edu.sa/',

        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-POST binding.
            // Leave blank to use the '{idpName}_acs' route, e.g. 'test_acs'
            'url' => env('APP_URL').'auth/iam/acs',
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        // Remove this part to not include any URL Location in the metadata.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-Redirect binding.
            // Leave blank to use the '{idpName}_sls' route, e.g. 'test_sls'
            'url' => '',
        ),
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array(
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => env('SAML2_'.$this_idp_env_id.'_IDP_SSO_URL', ''),
        
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_'.$this_idp_env_id.'_IDP_SSO_URL', ''),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',

        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_'.$this_idp_env_id.'_IDP_SL_URL',''),
        ),
        // Public x509 certificate of the IdP
        'x509cert' => env('SAML2_'.$this_idp_env_id.'_IDP_x509', ''),
        /*
         *  Instead of use the whole x509cert you can use a fingerprint
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it)
         */
        // 'certFingerprint' => '',
    ),

/*MIIEejCCA2KgAwIBAgIEVPNzWjANBgkqhkiG9w0BAQsFADBPMQswCQYDVQQGEwJz
        YTEMMAoGA1UEChMDbW9pMQwwCgYDVQQLEwNuaWMxDjAMBgNVBAsTBWluZnJhMRQw
        EgYDVQQDEwtpbmZyYSBjYSB2MjAeFw0xNzA0MDMwNTM4NDBaFw0yMjA0MDMwNjA4
        NDBaMGIxCzAJBgNVBAYTAnNhMQwwCgYDVQQKEwNtb2kxDDAKBgNVBAsTA25pYzEO
        MAwGA1UECxMFaW5mcmExDzANBgNVBAsTBnNlcnZlcjEWMBQGA1UEAxMNSUFNQXV0
        aFNpZ25lcjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAILmT0FlDQnO
        j17jPcQ0dwcdSZqXrDPXNNmAhMutgG1QStg1JHvtIKPYOiE8aE8zA8O34D2U3Nr7
        BrunXrIx1IFOglBYffe6Ituki9qteKMGmp8fllvM9/1vhIKP93E0Od0O9lrkjc7N
        0Iedw0TbulnH72hN3I7V7HbpGIfQwaZ1LKGOKLTr2BmigYLlMtICRdJFDmkSETtq
        2VWuTCZWL4CkYZgi2dmLTuD3146c40rsZwq3KQQ4FFBRGJQBmM4yJ5exE1zNYvC8
        id+tq1H53IgGNi/nV5pUTDXz/difkUElSD1z2w9I3glgH7lpQCo9TP7VVXXs0jXZ
        1KAoqWo8bB8CAwEAAaOCAUkwggFFMAsGA1UdDwQEAwIFoDAdBgNVHSUEFjAUBggr
        BgEFBQcDAQYIKwYBBQUHAwIwEQYJYIZIAYb4QgEBBAQDAgZAMHEGA1UdHwRqMGgw
        ZqBkoGKkYDBeMQswCQYDVQQGEwJzYTEMMAoGA1UEChMDbW9pMQwwCgYDVQQLEwNu
        aWMxDjAMBgNVBAsTBWluZnJhMRQwEgYDVQQDEwtpbmZyYSBjYSB2MjENMAsGA1UE
        AxMEQ1JMMTArBgNVHRAEJDAigA8yMDE3MDQwMzA1Mzg0MFqBDzIwMjAxMDAyMTAw
        ODQwWjAfBgNVHSMEGDAWgBR5Yj4i+5gh1oPH/gZDoweDT7P2/TAdBgNVHQ4EFgQU
        v11rbBEmGdCSABF908WNAdjHQj8wCQYDVR0TBAIwADAZBgkqhkiG9n0HQQAEDDAK
        GwRWOC4xAwIDqDANBgkqhkiG9w0BAQsFAAOCAQEAHIylgUhiVS2HlJ8eOhw/6XLj
        bRIW8xnozxFO37uxyzOJYz8cDLmKRS/Dn3NkkyngEiADMMwB/dmlgE72CKU+/GqP
        PQzmcbTmV7LGRDqZ70i0X8WOsINnYvtn5Mo13d/guqrNNCTFM9+pwwYSkrbOabiB
        bZuXczYRDVuUDuGP03N4/slv9HtJxiORB/WVZeh9J6wrqoQw/txm/QfHXPXqpRd6
        JMqHY39ndWe8xEHz1K89qPAlG7ScfxJW941z5apQ0k3MVtuQfhgYE7W9IUzWqlwa
        dfaEBeU7q5xR3aYeKU/Yg7DX+t8oii48NHJxpE/mszRr52NsmY4Ln1BQVv6pig==*/


        /* certifcate of response
        MIIESzCCAzOgAwIBAgIEVGt65zANBgkqhkiG9w0BAQsFADBSMQswCQYDVQQGEwJz
        YTEMMAoGA1UEChMDbW9pMQwwCgYDVQQLEwNuaWMxDjAMBgNVBAsTBWluZnJhMRcw
        FQYDVQQDEw5JbmZyYSBDQSBWMiBQUDAeFw0xNzA2MTkxMTIxMjZaFw0yMDA2MTkx
        MTUxMjZaMF8xCzAJBgNVBAYTAnNhMQwwCgYDVQQKEwNtb2kxDDAKBgNVBAsTA25p
        YzEOMAwGA1UECxMFaW5mcmExDzANBgNVBAsTBnNlcnZlcjETMBEGA1UEAxMKaWFt
        Lmdvdi5zYTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAKviSOWcHeJD
        rEVpjOaU6M7Tppa/BnHxJ0sOwODjFp1VaRubQRY/zT7Nq/UvqsS6DUKSUw/WV/yc
        E0OOazEvTx9PIXgdihpYgljgLxtAuIJ+3KGCNZNuFz8dDrZF7h3lBBc9CaMpBrY4
        70WKh7XTvQjvk2PLawKnxw2/yVsdJg0WyA4LY2i2gnGTBjuVPeaHXoOfkGTwB4U5
        zHcGMDtK06Ous6x/lhrLLkasCgfE+AkhrnFSLYi330KiFbc5hqiOzzFymYJql2sP
        TjXPD6GfbCVwbYGdQSlFJfy+Rky7qewpUQ6XSIj+8rzoSv+0dIaderaYthyZuEiT
        argeXOC3Zs0CAwEAAaOCARowggEWMAsGA1UdDwQEAwIHgDB0BgNVHR8EbTBrMGmg
        Z6BlpGMwYTELMAkGA1UEBhMCc2ExDDAKBgNVBAoTA21vaTEMMAoGA1UECxMDbmlj
        MQ4wDAYDVQQLEwVpbmZyYTEXMBUGA1UEAxMOSW5mcmEgQ0EgVjIgUFAxDTALBgNV
        BAMTBENSTDEwKwYDVR0QBCQwIoAPMjAxNzA2MTkxMTIxMjZagQ8yMDE5MDcyNjE1
        NTEyNlowHwYDVR0jBBgwFoAUZYqvso+WtZ6o3CNQ03tsT50HNCQwHQYDVR0OBBYE
        FHXOfB4RlrXEqec5K+4UF+zBfBvYMAkGA1UdEwQCMAAwGQYJKoZIhvZ9B0EABAww
        ChsEVjguMQMCBLAwDQYJKoZIhvcNAQELBQADggEBAAnCk9/KlmVOxNxBPZijBOOg
        J8dNjT5pJOj26WotnmEYWjUNMyaMfu59DSTNeuJX22BVHymx+wTjrjV+OVE7yKjU
        MCP0CaH0aBuQ/BWKnTOmOz1Y2K9o4QUV7VoxVE8GaRrXc/zllqMG+KL6YNlZaul7
        +VjTRnCKYpLrxSNefLfllmSCZDmc7PR4wOzdz+l3aPXC4o65RQeqxt68fripIHpY
        8vY6GlGeLHR3vQYhBAnwQoRdYnYNou0D2JoQ5kGvGF4QbvOnYOlfVlMl4tYJJ8qu
        8Dx7KLntD1U9OEgNyGlQefT/iClmSV7I/8Aj59+6Cys76I17uHuefjOE7nBAOag=
        */

    /***
     *
     *  OneLogin advanced settings
     *
     *
     */
    // Security settings
    'security' => array(

        /** signatures and encryptions offered */

        // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        // will be encrypted.
        'nameIdEncrypted' => false,

        // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
        // will be signed.              [The Metadata of the SP will offer this info]
        'authnRequestsSigned' => true,

        // Indicates whether the <samlp:logoutRequest> messages sent by this SP
        // will be signed.
        'logoutRequestSigned' => true,

        // Indicates whether the <samlp:logoutResponse> messages sent by this SP
        // will be signed.
        'logoutResponseSigned' => true,

        /* Sign the Metadata
         False || True (use sp certs) || array (
                                                    keyFileName => 'metadata.key',
                                                    certFileName => 'metadata.crt'
                                                )
        */
        'signMetadata' => true,


        /** signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => true,

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed.        [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => true,

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => false,

        // Authentication context.
        // Set to false and no AuthContext will be sent in the AuthNRequest,
        // Set true or don't present thi parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
        'requestedAuthnContext' => true,
    ),

    // Contact information template, it is recommended to suply a technical and support contacts
    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'name',
            'emailAddress' => 'no@reply.com'
        ),
        'support' => array(
            'givenName' => 'Support',
            'emailAddress' => 'no@reply.com'
        ),
    ),

    // Organization information template, the info in en_US lang is recomended, add more if required
    'organization' => array(
        'en-US' => array(
            'name' => 'Majmaah University',
            'displayname' => 'Support',
            'url' => 'https://in.mu.edu.sa/'
        ),
    ),

/* Interoperable SAML 2.0 Web Browser SSO Profile [saml2int]   http://saml2int.org/profile/current

   'authnRequestsSigned' => false,    // SP SHOULD NOT sign the <samlp:AuthnRequest>,
                                      // MUST NOT assume that the IdP validates the sign
   'wantAssertionsSigned' => true,
   'wantAssertionsEncrypted' => true, // MUST be enabled if SSL/HTTPs is disabled
   'wantNameIdEncrypted' => false,
*/

);
