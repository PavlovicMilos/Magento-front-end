
/**
 * Magictoolbox iehover-fix.js for Magento store.
 * @mail support@magictoolbox.com
 */

function toggleMenu(el, over, second)
{
    if(!second && !over) {
        setTimeout(function(){toggleMenu(el, 0, 1);}, 100);
        el.setAttribute('sameAttribute',1);
        return;
    } else if(!over && el.getAttribute('sameAttribute') == 0) {
        return;
    }
    el.setAttribute('sameAttribute',0);

    if (Element.childElements(el)) {
    var uL = Element.childElements(el)[1];
    var iS = true;
    }
    if (over) {
        Element.addClassName(el, 'over');
        
        if(iS){ uL.addClassName('shown-sub')};
    }
    else {
        Element.removeClassName(el, 'over');
        if(iS){ uL.removeClassName('shown-sub')};
    }
}

ieHover = function() {
    var items, iframe;
    items = $$('#nav ul', '.truncated_full_value .item-options', '.tool-tip');
    $$('#checkout-step-payment', '.tool-tip').each(function(el) {
        el.show();
        el.setStyle({'visibility':'hidden'})
    })
    for (var j=0; j<items.length; j++) {
        iframe = document.createElement('IFRAME');
        iframe.src = BLANK_URL;
        iframe.scrolling = 'no';
        iframe.frameBorder = 0;
        iframe.className = 'hover-fix';
        iframe.style.width = items[j].offsetWidth+"px";
        iframe.style.height = items[j].offsetHeight+"px";
        items[j].insertBefore(iframe, items[j].firstChild);
    }
    $$('.tool-tip', '#checkout-step-payment').each(function(el) {
        el.hide();
        el.setStyle({'visibility':'visible'})
    })
}
Event.observe(window, 'load', ieHover);
