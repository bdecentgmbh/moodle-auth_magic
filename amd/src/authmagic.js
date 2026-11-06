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

    /**
     * Find the magic authentication identity-provider link on the login page.
     *
     * The link is matched by its href (the auth_magic login endpoint) so it keeps
     * working regardless of the surrounding login-page markup, which changes
     * between Moodle versions.
     *
     * @return {Element|null} The magic login link, or null if it is not present.
     */
    AuthMagic.prototype.getMagicLink = function() {
        return document.querySelector('a[href*="/auth/magic/login.php"]');
    };

    AuthMagic.prototype.magicLoginHook = function() {
        var self = this;
        var magicLink = self.getMagicLink();
        if (magicLink) {
            self.magicLoginHandler(magicLink, "form#login #username");
        }
    };

    AuthMagic.prototype.magicLoginHandler = function(magicLink, mailHandler) {
        magicLink.addEventListener("click", function(e) {
            e.preventDefault();

            var returnurl = e.currentTarget.getAttribute("href");
            var userValue = "";
            // Read the value entered in the login form's username field.
            var mailSelector = document.querySelector(mailHandler);
            if (mailSelector) {
                userValue = mailSelector.value;
            }

            // Create a form to post the request to the magic login endpoint.
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", returnurl);
            form.setAttribute("id", "magic-login-form");

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

            magicLink.parentNode.appendChild(form);

            document.querySelector("form#magic-login-form").submit();
        });
    };


    return {
        init: function(params) {
            return new AuthMagic(params);
        }
    };

});
