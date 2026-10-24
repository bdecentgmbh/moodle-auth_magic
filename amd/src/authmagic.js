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

define([], function() {

    /**
    * Controls Custom styles tool action.
    * @param {object} params
    */
    var AuthMagic = function(params) {
        var self = this;
        if (params.loginhook) {
            self.magicLoginHook(params);
        }
        return true;
    };


    AuthMagic.prototype.getMagicLink = function(params) {
        // Find the button by its address, its label differs between Moodle versions and themes.
        return document.querySelector("#page-login-index a[href=\"" + params.linkurl + "\"]");
    };

    AuthMagic.prototype.magicLoginHook = function(params) {
        var self = this;
        var MagicLink = self.getMagicLink(params);
        if (MagicLink) {
            self.magicLoginHandler(MagicLink, "form#login #username");
        }
    };

    AuthMagic.prototype.magicLoginHandler = function(MagicLink, mailHandler) {
        MagicLink.addEventListener("click", function(e) {
            var returnurl = e.currentTarget.getAttribute("href");
            var userValue = "";
            //form#login #username
            var mailSelector = document.querySelectorAll(mailHandler)[0];
            if (mailSelector) {
                userValue = mailSelector.value.trim();
            }
            if (!userValue) {
                // Nothing typed yet: follow the link to the page that asks for the email address.
                return;
            }
            e.preventDefault();

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

            // The login token of the login form shows that the request comes from this site.
            var tokenField = document.querySelector("form#login input[name=\"logintoken\"]");
            var loginToken = document.createElement("input");
            loginToken.setAttribute("type", "hidden");
            loginToken.setAttribute("name", "logintoken");
            loginToken.setAttribute("value", tokenField ? tokenField.value : "");

            form.appendChild(magicLogin);
            form.appendChild(uservalue);
            form.appendChild(loginToken);

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
