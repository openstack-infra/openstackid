<?php
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

namespace oauth2;

/**
 * Class AddressClaim
 * @package oauth2
 * http://openid.net/specs/openid-connect-core-1_0.html#AddressClaim
 *
 * The Address Claim represents a physical mailing address. Implementations MAY return only a subset of the fields of an
 * address, depending upon the information available and the End-User's privacy preferences. For example, the country
 * and region might be returned without returning more fine-grained address information.
 * Implementations MAY return just the full address as a single string in the formatted sub-field, or they MAY return
 * just the individual component fields using the other sub-fields, or they MAY return both. If both variants are
 * returned, they SHOULD be describing the same address, with the formatted address indicating how the component fields
 * are combined.
 */
abstract class AddressClaim
{
    /**
     * Full mailing address, formatted for display or use on a mailing label. This field MAY contain multiple lines,
     * separated by newlines. Newlines can be represented either as a carriage return/line feed pair ("\r\n") or as a
     * single line feed character ("\n").
     */
    const Formatted = 'formatted';

    /**
     * Full street address component, which MAY include house number, street name, Post Office Box, and multi-line
     * extended street address information. This field MAY contain multiple lines, separated by newlines. Newlines
     * can be represented either as a carriage return/line feed pair ("\r\n") or as a single line feed character ("\n").
     */
    const StreetAddress = 'street_address';

    /**
     * City or locality component.
     */
    const Locality = 'locality';

    /**
     * State, province, prefecture, or region component.
     */
    const Region = 'region';

    /**
     * Zip code or postal code component.
     */
    const PostalCode = 'postal_code';

    /**
     * Country name component.
     */
    const Country = 'country';

}