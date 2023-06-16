// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Magic authentication define js.
 * @module   auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str', 'core/ajax'],
function(String, Ajax) {

    /**
    * Controls Custom styles tool action.
    * @param {object} params
    */
    var AuthMagic = function(params) {
        var self = this;
        if (params.loginhook) {
            self.magicLoginHook(params);
        }
        if (params.customloginhook) {
            self.magicCustomLoginHook(params);
        }
        return true;
    };

    AuthMagic.prototype.magicCustomLoginHook = function(params) {
        var self = this;
        var MagicLink = self.getMagicLink(params, "#page-local-magic-login");
        if (MagicLink) {
            self.magicLoginHandler(MagicLink, "form#login #id_email", params);
        }
    };

    AuthMagic.prototype.getMagicLink = function(params, linkId) {
        var authSelector = linkId + " .potentialidplist a[title=\"" + params.strbutton + "\"]";
        var MagicLink = "";
        if (authSelector) {
            MagicLink = document.querySelectorAll(authSelector)[0];
            if (MagicLink === undefined) {
                authSelector = linkId + " .login-identityproviders a";
                var MagicLinks = document.querySelectorAll(authSelector);
                if (MagicLinks.length) {
                    MagicLinks.forEach(function(item) {
                        var inner = item.innerHTML.trim();
                        if (inner == params.strbutton) {
                            MagicLink = item;
                        }
                    });
                }
            }
        }
        return MagicLink;
    };

    AuthMagic.prototype.magicLoginHook = function(params) {
        var self = this;
        var linkId = "#page-login-index";
        var MagicLink = self.getMagicLink(params, linkId);
        if (MagicLink) {
            if (!document.querySelector("#page-login-index .potentialidplist")) {
                MagicLink.classList.remove("btn-secondary");
                MagicLink.classList.add("btn-primary");
                potentialiDpList = document.querySelectorAll(linkId + " .login-identityproviders a");
                potentialiDp = document.querySelectorAll(linkId + " .login-identityproviders h2")[0];
            } else {
                var potentialiDp = document.querySelector(linkId + " .potentialidplist");
                if (potentialiDp) {
                    potentialiDp = potentialiDp.previousElementSibling;
                }
                var potentialiDpList = document.querySelectorAll(linkId + " .potentialidplist .potentialidp");
            }
            if (!params.linkbtnpos) {
                params.linkbtnpos = 0;
            }
            if (params.linkbtnpos == 0) {
                var MagicLinkBlock = document.querySelectorAll("#page-login-index form#login .form-group")[params.linkbtnpos];
                if (MagicLinkBlock) {
                    MagicLinkBlock.appendChild(MagicLink);
                    // Create a span.
                    var span = document.createElement("span");
                    span.setAttribute("class", "magic-password-instruction");
                    var passInfo = String.get_string('passinfo', 'auth_magic');
                    passInfo.done(function(localizedEditString) {
                        span.innerHTML = localizedEditString;
                    });
                    MagicLinkBlock.appendChild(span);
                }
            }
            if (params.linkbtnpos == 1) {
                var MagicLinkBlock = document.querySelectorAll("#page-login-index form#login .form-group")[params.linkbtnpos];
                if (MagicLinkBlock) {
                    MagicLinkBlock.appendChild(MagicLink);
                }
            }

            if (params.linkbtnpos <= 1 ) {
                if (potentialiDpList.length <= 1) {
                    potentialiDp.style.display = 'none';
                    var identityProvider = document.querySelectorAll("#page-login-index .login-identityproviders")[0];
                    if (identityProvider) {
                        identityProvider.previousElementSibling.style.display = 'none';
                        identityProvider.nextElementSibling.style.display = 'none';
                    }
                }
            }

            if (params.linkbtnpos == 2) {
                MagicLink.classList.remove("btn-primary");
            }
            self.magicLoginHandler(MagicLink, "form#login #username", params);
        }
    };

    AuthMagic.prototype.getMagicLinkPassCheck = function() {
        var emailElement = document.querySelector("form.login-form input[name=email]");
        var passwordElement = document.querySelector("form.login-form input[name=password]");
        var status = false;
        if (emailElement && passwordElement) {
            var args = {
                email : emailElement.value,
                password : passwordElement.value,
            };
            Ajax.call([{
                methodname: 'auth_magic_get_magiclink_passcheck',
                args: args,
                done: function(response) {
                    status = response.status;
                }
            }], status);
        }
        return status;
    };

    AuthMagic.prototype.magicLoginHandler = function(MagicLink, mailHandler, params) {
        var self = this;
        MagicLink.addEventListener("click", function(e) {
            e.preventDefault();
            if (params.passcheck) {
                // Wrong password condition give the redirect to the current data.
                var loginformbutton = document.querySelector("form.login-form .magic-submit-action input");
                loginformbutton.click();
            }

            if (params.passcheck && !self.getMagicLinkPassCheck()) {
                return "";
            }
            var returnurl = e.currentTarget.getAttribute("href");
            var userValue = "";
            //form#login #username
            var mailSelector = document.querySelectorAll(mailHandler)[0];
            if (mailSelector) {
                userValue = mailSelector.value;
            }
            // Create a form.
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", returnurl);
            form.setAttribute("id", "magic-login-form");

                // Create an input element for Full Name
            var magicLogin = document.createElement("input");
            magicLogin.setAttribute("type", "hidden");
            magicLogin.setAttribute("name", "magiclogin");
            magicLogin.setAttribute("value", 1);

            var uservalue = document.createElement("input");
            uservalue.setAttribute("type", "hidden");
            uservalue.setAttribute("name", "uservalue");
            uservalue.setAttribute("value", userValue);

            form.appendChild(magicLogin);
            form.appendChild(uservalue);

            MagicLink.parentNode.appendChild(form);

            var magicForm = document.querySelectorAll("form#magic-login-form")[0];
            magicForm.submit();
        });
    };

    return {
        init: function(params) {
            return new AuthMagic(params);
        }
    };

});