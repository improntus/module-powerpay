define([], function () {
    'use strict';

    let FRAME_ID = 'mo-offer-frame';

    /**
     * <mo-offer-frame> never removes itself: its close button only dispatches a
     * document-level "closeIframe" event, and the removal is done by whichever
     * Powerpay component is on the page (mo-product-page, mo-button, mo-link).
     * The neutral widget renders none of them, so it must remove the frame itself.
     */
    function removeFrame() {
        let frame = document.getElementById(FRAME_ID);

        if (frame) {
            frame.remove();
        }
    }

    document.addEventListener('closeIframe', removeFrame);

    /**
     * Opens Powerpay's offer modal by injecting the <mo-offer-frame> custom element
     * provided by the Powerpay components bundle (loaded in head.phtml).
     */
    return function (config, element) {
        element.addEventListener('click', function (event) {
            event.preventDefault();
            removeFrame();

            let frame = document.createElement('mo-offer-frame');

            frame.id = FRAME_ID;
            frame.setAttribute('client-id', config.clientId);
            frame.setAttribute('price', config.price);
            frame.setAttribute('theme', '');
            document.body.appendChild(frame);
        });
    };
});
