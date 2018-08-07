(function( $ ){

    /**
     * @param string message
     * @returns string
     */
    function hash(message)
    {
        var hash = CryptoJS.SHA256(message).toString();
        console.log('calculated hash '+hash+' from message '+message);
        return hash;
    }

    /**
     *
     * @param string clientId
     * @param string origin
     * @param string opbs
     * @param string salt
     * @returns string
     */
    function computeSessionStateHash(clientId, origin, opbs, salt)
    {
        return hash(clientId + origin + opbs + salt);
    }

    /**
     *
     * @param origin
     * @param message
     * @returns string
     */
    function calculateSessionStateResult(origin, message) {
        try
        {

            if (!origin || !message)
            {
                return "error";
            }

            var messageParts = message.split(' ');
            if (messageParts.length !== 2)
            {
                return "error";
            }

            var clientId     = messageParts[0];
            var sessionState = messageParts[1];

            if (!clientId || !sessionState)
            {
                return "error";
            }

            var sessionStateParts = sessionState.split('.');
            if (sessionStateParts.length !== 2)
            {
                return "error";
            }

            var clientHash = sessionStateParts[0];
            var salt       = sessionStateParts[1];

            if (!clientHash || !salt)
            {
                return "error";
            }

            var opbs         = $.cookie('opbs');
            var expectedHash = computeSessionStateHash(clientId, origin, opbs, salt);
            return clientHash === expectedHash ? "unchanged" : "changed";
        }
        catch(e)
        {
            return "error";
        }
    }

    if (window.parent !== window)
    {
        window.addEventListener("message", function (e)
        {
            var result = calculateSessionStateResult(e.origin, e.data);
            e.source.postMessage(result, e.origin);
        }, false);
    }


}( jQuery ));