<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            'class' => 'magicslideshow-edit-form'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();

    }

    protected function _afterToHtml($html)
    {

        $html .= '
<script type="text/javascript">

    getElementsByClass = function(classList, node) {
        var node = node || document;
        if (node.getElementsByClassName) {
            return node.getElementsByClassName(classList);
        } else {
            var nodes = node.getElementsByTagName("*"),
            nodesLength = nodes.length,
            classes = classList.split(/\s+/),
            classesLength = classes.length,
            result = [], i,j;
            for (i = 0; i < nodesLength; i++) {
                for (j = 0; j < classesLength; j++)  {
                    if (nodes[i].className.search("\\\\b" + classes[j] + "\\\\b") != -1) {
                        result.push(nodes[i]);
                        break;
                    }
                }
            }
            return result;
        }
    }

    var fieldsets = getElementsByClass("magicslideshow-fieldset");
    var header = null;
    var buttons = null;
    var magicslideshowFieldsetId = "";
    for (var i = 0, l = fieldsets.length; i < l; i++) {
        header = fieldsets[i].previousSibling;
        while (header.nodeType!=1) {
            header = header.previousSibling;
        }
        header.style.cursor = "pointer";
        buttons = getElementsByClass("form-buttons", header);
        buttons[0].innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        buttons[0].className += " fieldset-open";
        header.onclick = function() {
            var buttons = getElementsByClass("form-buttons", this);
            var fieldset = this.nextSibling;
            while (fieldset.nodeType!=1) {
                fieldset = fieldset.nextSibling;
            }
            if (buttons[0].className.match(/\bfieldset\-open\b/)) {
                buttons[0].className = buttons[0].className.replace(/\bfieldset\-open\b/, "fieldset-close");
                fieldset.style.display = "none";
                this.style.marginBottom = "5px";
            } else {
                buttons[0].className = buttons[0].className.replace(/\bfieldset\-close\b/, "fieldset-open");
                fieldset.style.display = "block";
                this.style.marginBottom = "0px";
            }
            return false;
        }
        var id = fieldsets[i].id.replace(/_group_fieldset_\d+/g, "");
        if (magicslideshowFieldsetId != id) {
            magicslideshowFieldsetId = id;
        } else {
            header.click();
        }
    }

    var magictoolboxPofiles = {};
    var magictoolboxOptions = getElementsByClass("magictoolbox-option");
    var magictoolboxOptionsLength = magictoolboxOptions.length;
    for (var i = 0; i < magictoolboxOptionsLength; i++) {
        var optionName = magictoolboxOptions[i].getAttribute("name");
        var idMatch = optionName.match(/^magicslideshow\[([^\[]+)\]\[([^\[]+)\]\[([^\[]+)\]/i);
        if (optionName && idMatch) {
            magictoolboxOptions[i].setAttribute("data-platform", idMatch[1]);
            magictoolboxOptions[i].setAttribute("data-profile", idMatch[2]);
            magictoolboxOptions[i].setAttribute("data-id", idMatch[3]);
            magictoolboxPofiles[idMatch[2]] = idMatch[2];
            if (idMatch[2] == "default") {
                if (magictoolboxOptions[i].tagName.toLowerCase() == "select" ||
                   magictoolboxOptions[i].getAttribute("type") == "text" ||
                   (magictoolboxOptions[i].getAttribute("type") == "radio" && magictoolboxOptions[i].checked)) {
                    magictoolboxOptions[i].setAttribute("data-default", magictoolboxOptions[i].value);
                }
                magictoolboxOptions[i].onchange = function() {
                    var thisName = this.getAttribute("name");
                    var thisPlatform = this.getAttribute("data-platform");
                    var thisId = this.getAttribute("data-id");
                    for (var profile in magictoolboxPofiles) {
                        var elements = document.getElementsByName("magicslideshow["+thisPlatform+"]["+profile+"]["+thisId+"]");
                        if (elements.length) {
                            if ((elements[0].tagName.toLowerCase() == "select" || elements[0].getAttribute("type") == "text") &&
                                (this.getAttribute("data-default") == elements[0].value)) {
                                 elements[0].value = this.value;
                            } else if (elements[0].getAttribute("type") == "radio") {
                                var radios = document.getElementsByName("magicslideshow["+thisPlatform+"][default]["+thisId+"]");
                                var defaultValue = "";
                                var j;
                                for (j = 0; j < radios.length; j++) {
                                    if (radios[j].getAttribute("data-default")) {
                                        defaultValue = radios[j].getAttribute("data-default");
                                        break;
                                    }
                                }
                                for (j = 0; j < elements.length; j++) {
                                    if (elements[j].checked) {
                                        break;
                                    }
                                }
                                if (j != elements.length) {
                                    //NOTE: found checked element
                                    if (defaultValue == elements[j].value) {
                                        for (k = 0; k < elements.length; k++) {
                                            elements[k].checked = false;
                                            if (this.value == elements[k].value) {
                                                elements[k].checked = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                };
            }
        }
    }


</script>
';

        return parent::_afterToHtml($html);

    }

}
