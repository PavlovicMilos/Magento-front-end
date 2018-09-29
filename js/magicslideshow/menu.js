
/**
 * Magictoolbox menu.js for Magento store.
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
    
    if (over) {
        Element.addClassName(el, 'over');
    }
    else {
        Element.removeClassName(el, 'over');
    }
}
