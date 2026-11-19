<x-legal-page title="Privacy Policy" :updated="$updated">
    <h2>1. Controller</h2>
    <p>The controller of your personal data is <x-operator :op="$op" />. You can write to this e-mail address about anything concerning your personal data.</p>

    <h2>2. What data we process and why</h2>
    <ul>
        <li><strong>Account</strong> – e-mail, password (hashed), phone number, language, role (driver, company, advertiser). Purpose: creating and running your account, contact, security. Legal basis: contract (Art. 6(1)(b) GDPR).</li>
        <li><strong>Driver profile</strong> – first name, surname, experience, licences, vehicle types, whether you want to appear in the driver database. Companies see only your first name, the first letter of your surname and your qualifications; your phone number only when you accept an invitation or apply yourself. Legal basis: contract.</li>
        <li><strong>Company data</strong> – name, VAT number, type of business, content of ads. Legal basis: contract.</li>
        <li><strong>Ads, map reports, CB messages</strong> – content, place of the report, time. They are visible to other users for a limited time (e.g. CB messages 3 hours, a traffic jam 2 hours). Legal basis: contract.</li>
        <li><strong>Location</strong> – collected only when you allow it in your browser and only for a specific feature:
            <ul>
                <li>map reports and SOS calls – we save the place of the report; for SOS, other drivers see the place and may see your phone number to contact you,</li>
                <li>drive mode – your position is used on your phone for warnings; only approximate information needed to determine the country (for reports and the CB channel) is sent to our server,</li>
                <li>nearby SOS notifications – we store your last known position (updated at most every 10 minutes in drive mode) so we can send you calls from drivers within your chosen radius; positions older than 24 hours are not used.</li>
            </ul>
            Legal basis: contract, and for notifications your consent (Art. 6(1)(a) GDPR), which you can withdraw by turning notifications off.</li>
        <li><strong>Voice CB</strong> – your voice is transmitted live to people on the channel and <strong>is not recorded</strong>. Others see your signature (first name and first letter of surname).</li>
        <li><strong>Route planner and address search</strong> – addresses and route points you enter are sent to the routing server and address search only to calculate the route; they are not linked to your account.</li>
        <li><strong>Technical data</strong> – IP address, browser type, server logs. Purpose: security and abuse prevention. Legal basis: legitimate interest (Art. 6(1)(f) GDPR).</li>
    </ul>

    <h2>3. Who we share data with</h2>
    <ul>
        <li>the hosting provider on whose servers the Portal runs,</li>
        <li>the provider of the routing and voice CB server (when these features are running),</li>
        <li>the SMS gateway operator – your phone number, when we send a verification code,</li>
        <li>push notification services chosen by your browser or phone (e.g. Google, Apple, Mozilla) – the encrypted content of the notification,</li>
        <li>the OpenStreetMap Foundation (map tiles) and the Nominatim service (address search) – your browser loads the map directly from their servers, so they learn your IP address,</li>
        <li>other users – the content you publish yourself,</li>
        <li>public authorities – only where required by law.</li>
    </ul>
    <p>Some of these providers (e.g. notification services) may process data outside the European Economic Area. This is based on a European Commission adequacy decision or standard contractual clauses.</p>

    <h2>4. How long we keep data</h2>
    <ul>
        <li>account data – until the account is deleted,</li>
        <li>ads – visible for 30 days, then kept until the account is deleted (as your archive),</li>
        <li>map reports and CB messages – hidden automatically after a short time and deleted together with the account,</li>
        <li>position for notifications – until you turn notifications off or delete the account,</li>
        <li>technical logs – up to 12 months.</li>
    </ul>

    <h2>5. Your rights</h2>
    <p>You have the right to access, rectify and erase your data, to restrict processing, to data portability, to object to processing based on legitimate interest and to withdraw consent at any time. You can delete your account and all data yourself under <a href="{{ route('account') }}" class="text-k19-red">My account</a>. You may also lodge a complaint with the Polish supervisory authority, the President of the Personal Data Protection Office (ul. Stawki 2, 00-193 Warsaw), or the authority in your country.</p>

    <h2>6. Cookies and browser storage</h2>
    <p>We use only essential cookies: session (login), form protection (CSRF) and your chosen language. In browser storage we keep convenience settings (e.g. last vehicle in the planner, CB channel, warning sound). We do not use advertising or tracking cookies and do not profile users.</p>

    <h2>7. Changes</h2>
    <p>We will inform you in the Portal about significant changes to this Privacy Policy. The current version is always available at kanal19.pl/polityka-prywatnosci.</p>
</x-legal-page>
