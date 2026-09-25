<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Privacy Policy" />

  <div
    class="flex min-h-screen flex-col bg-background px-4 pt-8 pb-12 text-foreground sm:px-6 lg:px-8"
  >
    <!-- Back Button Wrapper -->
    <div class="mx-auto mb-4 w-full max-w-4xl">
      <Link
        href="/"
        class="inline-flex items-center text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="mr-2 h-4 w-4"
        >
          <path d="m15 18-6-6 6-6" />
        </svg>
        Back to Home
      </Link>
    </div>

    <main
      class="mx-auto w-full max-w-4xl flex-grow rounded-lg border border-border bg-card p-6 shadow-sm md:p-12"
    >
      <h1
        class="mb-8 border-b border-border pb-4 text-3xl font-bold md:text-4xl"
      >
        PRIVACY POLICY
      </h1>

      <div class="max-w-none space-y-8 leading-relaxed text-muted-foreground">
        <!-- Introduction Section -->
        <div class="space-y-4">
          <p class="font-semibold text-foreground">
            FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC)
            DIGITAL PLATFORM
          </p>
          <!-- <strong>Effective Date:</strong> [Insert Effective Date] <br> -->
          <p><strong>Last Updated:</strong> September 1, 2025</p>
          <p>
            The FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC)
            ("FISMPC", "the Cooperative", "we", "our", or "us") respects your
            privacy and is committed to protecting the personal information of
            users of the FISMPC Digital Platform ("Platform").
          </p>
          <p>
            This Privacy Policy explains how FISMPC collects, uses, stores,
            protects, and processes personal information when you register for,
            access, or use the FISMPC mobile application and related digital
            services.
          </p>
          <p>
            By creating an account or using the Platform, you acknowledge that
            you have read and understood this Privacy Policy and consent to the
            collection and processing of your personal information in accordance
            with applicable laws and regulations.
          </p>
        </div>

        <!-- Section 1 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            1. Information We Collect
          </h2>
          <p>
            FISMPC may collect personal information that you provide directly
            through the Platform or that is generated through your use of the
            Platform.
          </p>

          <h3 class="mt-4 font-semibold text-foreground">
            1.1 Registration and Account Information
          </h3>
          <p>During account registration, we may collect:</p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Full name;</li>
            <li>Mobile number;</li>
            <li>One-Time Password (OTP) verification information;</li>
            <li>Password and authentication-related information;</li>
            <li>Account status;</li>
            <li>Registration and verification records; and</li>
            <li>
              Other information necessary to create and secure your account.
            </li>
          </ul>
          <p>
            Passwords should be stored and processed using appropriate security
            measures. FISMPC does not intend to display or disclose users'
            passwords in readable form.
          </p>

          <h3 class="mt-4 font-semibold text-foreground">
            1.2 Member Profile Information
          </h3>
          <p>When completing your profile, we may collect:</p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Email address;</li>
            <li>Gender;</li>
            <li>Date of birth;</li>
            <li>Residential or mailing address;</li>
            <li>Other profile information required by FISMPC; and</li>
            <li>
              Information necessary to verify your identity and eligibility for
              Cooperative services.
            </li>
          </ul>

          <h3 class="mt-4 font-semibold text-foreground">
            1.3 Identification Information
          </h3>
          <p>For member verification, FISMPC may collect:</p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Type of identification document;</li>
            <li>Identification document number;</li>
            <li>Front image of the identification document;</li>
            <li>Back image of the identification document; and</li>
            <li>Other information necessary for identity verification.</li>
          </ul>
          <p>
            Identification information and documents may be reviewed by
            authorized FISMPC personnel for verification purposes.
          </p>

          <h3 class="mt-4 font-semibold text-foreground">
            1.4 Membership Information
          </h3>
          <p>
            When you apply for or maintain Cooperative membership, we may
            collect and process:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Membership application information;</li>
            <li>Membership status;</li>
            <li>Membership verification records;</li>
            <li>Membership payment information;</li>
            <li>Payment history;</li>
            <li>Membership activation information; and</li>
            <li>
              Other information required for Cooperative membership
              administration.
            </li>
          </ul>

          <h3 class="mt-4 font-semibold text-foreground">
            1.5 Payment Information
          </h3>
          <p>
            When you make payments through the Platform, we may collect
            information necessary to identify and record the transaction, such
            as:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Transaction reference numbers;</li>
            <li>Payment amount;</li>
            <li>Payment date and time;</li>
            <li>Payment status;</li>
            <li>Payment method;</li>
            <li>Membership payment records; and</li>
            <li>
              Other transaction-related information provided by the applicable
              payment provider.
            </li>
          </ul>
          <p>
            Payments may be processed through third-party payment providers such
            as PayMongo. FISMPC does not intend to collect or store complete
            debit card or credit card credentials through the Platform. Payment
            credentials may be handled directly by the applicable payment
            provider according to its own privacy and security practices.
          </p>

          <h3 class="mt-4 font-semibold text-foreground">
            1.6 Digital Wallet Information
          </h3>
          <p>
            If you are eligible to use the FISMPC Digital Wallet, we may collect
            and process information necessary to operate and secure the wallet,
            including:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Wallet account information;</li>
            <li>Wallet balance information;</li>
            <li>Transaction records;</li>
            <li>Payment or transfer records;</li>
            <li>Transaction references;</li>
            <li>Transaction status;</li>
            <li>Security and verification information; and</li>
            <li>
              Other information necessary to provide authorized wallet services.
            </li>
          </ul>

          <h3 class="mt-4 font-semibold text-foreground">
            1.7 News, Training, and Platform Activity
          </h3>
          <p>
            When you use Platform services, FISMPC may maintain records related
            to your use of available services, including:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Business Training access or participation records;</li>
            <li>News and Events interactions;</li>
            <li>Membership service activity;</li>
            <li>Platform service usage;</li>
            <li>Support requests; and</li>
            <li>
              Other records necessary to provide and improve the Platform.
            </li>
          </ul>

          <h3 class="mt-4 font-semibold text-foreground">
            1.8 Device and Technical Information
          </h3>
          <p>
            The Platform may collect limited technical information necessary for
            security, troubleshooting, performance monitoring, and service
            operation. Depending on the features and technologies used by the
            Platform, this may include:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Device type and model;</li>
            <li>Operating system and version;</li>
            <li>Application version;</li>
            <li>
              Device identifiers or similar technical identifiers where
              applicable;
            </li>
            <li>IP address;</li>
            <li>Network or connection information;</li>
            <li>Error and diagnostic information; and</li>
            <li>Technical logs related to Platform activity.</li>
          </ul>
          <p>
            FISMPC will only collect technical information that is reasonably
            necessary for legitimate Platform purposes.
          </p>
        </div>

        <!-- Section 2 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            2. How We Use Personal Information
          </h2>
          <p>
            FISMPC may use collected personal information for the following
            purposes:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Creating and managing user accounts;</li>
            <li>Verifying mobile numbers and user identities;</li>
            <li>Verifying member profiles;</li>
            <li>Reviewing identification documents;</li>
            <li>Processing Cooperative membership applications;</li>
            <li>Managing membership records;</li>
            <li>Processing membership payments;</li>
            <li>Recording and confirming transactions;</li>
            <li>Providing authorized Digital Wallet services;</li>
            <li>Providing Business Training services;</li>
            <li>Providing News and Events information;</li>
            <li>Providing Cooperative financial transparency information;</li>
            <li>Responding to support requests and communications;</li>
            <li>Protecting accounts and Platform systems;</li>
            <li>
              Detecting and preventing fraud, unauthorized access, and abuse;
            </li>
            <li>Investigating suspicious or unauthorized transactions;</li>
            <li>Maintaining Platform security;</li>
            <li>Troubleshooting technical issues;</li>
            <li>Improving Platform performance and functionality;</li>
            <li>Complying with Cooperative policies;</li>
            <li>
              Complying with applicable legal and regulatory requirements; and
            </li>
            <li>
              Performing other legitimate activities necessary to operate the
              FISMPC Digital Platform.
            </li>
          </ul>
          <p>
            FISMPC will not use personal information for purposes that are
            incompatible with the purposes described in this Privacy Policy
            unless permitted or required by applicable law or otherwise properly
            disclosed to the user.
          </p>
        </div>

        <!-- Section 3 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            3. Legal Basis for Processing
          </h2>
          <p>
            FISMPC may process personal information when necessary for purposes
            such as:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Providing services requested by the user;</li>
            <li>Creating and maintaining user accounts;</li>
            <li>Processing Cooperative membership applications;</li>
            <li>Processing payments and transactions;</li>
            <li>Verifying user identity and eligibility;</li>
            <li>Complying with legal or regulatory obligations;</li>
            <li>Protecting the security and integrity of the Platform;</li>
            <li>Preventing fraud and unauthorized activities;</li>
            <li>Fulfilling legitimate Cooperative operational purposes; and</li>
            <li>
              Other lawful purposes permitted under applicable Philippine laws.
            </li>
          </ul>
          <p>
            Where consent is required by applicable law, FISMPC will obtain the
            appropriate consent from the user.
          </p>
        </div>

        <!-- Section 4 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            4. Identification and Verification
          </h2>
          <p>
            Certain FISMPC services require identity and profile verification.
            Identification documents and related personal information may be
            reviewed by authorized FISMPC personnel to:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Confirm the identity of the user;</li>
            <li>Verify membership eligibility;</li>
            <li>Prevent fraudulent applications;</li>
            <li>Protect the Cooperative and its members;</li>
            <li>Maintain accurate membership records; and</li>
            <li>Comply with applicable requirements.</li>
          </ul>
          <p>
            Access to certain services may be restricted while verification is
            pending.
          </p>
        </div>

        <!-- Section 5 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            5. Payment Providers and Third-Party Services
          </h2>
          <p>
            FISMPC may use third-party service providers to support Platform
            functionality. These providers may include payment processors,
            technology providers, communication services, hosting providers,
            analytics or diagnostic services, and other service providers
            necessary for Platform operations.
          </p>
          <p>
            For example, PayMongo may process applicable online payment
            transactions.
          </p>
          <p>
            Third-party providers may process information according to their own
            privacy policies and applicable legal requirements. FISMPC will take
            reasonable steps to work with service providers that are appropriate
            for the services they provide and that implement reasonable security
            measures.
          </p>
        </div>

        <!-- Section 6 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            6. Sharing of Personal Information
          </h2>
          <p>
            FISMPC does not sell users' personal information. FISMPC may
            disclose or share personal information when reasonably necessary
            with:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Authorized FISMPC personnel;</li>
            <li>Authorized Cooperative officers or administrators;</li>
            <li>Payment processors and financial service providers;</li>
            <li>Technology and hosting service providers;</li>
            <li>Service providers supporting Platform operations;</li>
            <li>
              Government agencies or regulatory authorities when required or
              permitted by law;
            </li>
            <li>Law enforcement authorities when legally required; or</li>
            <li>
              Other parties when disclosure is necessary to protect the rights,
              safety, security, property, or legitimate interests of FISMPC, its
              members, users, or other persons.
            </li>
          </ul>
          <p>
            FISMPC will limit access to personal information to persons or
            organizations that have a legitimate need to access the information.
          </p>
        </div>

        <!-- Section 7 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            7. Protection and Security of Personal Information
          </h2>
          <p>
            FISMPC implements reasonable organizational, technical, and physical
            safeguards designed to protect personal information from:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Unauthorized access;</li>
            <li>Unauthorized disclosure;</li>
            <li>Loss;</li>
            <li>Misuse;</li>
            <li>Alteration;</li>
            <li>Destruction; and</li>
            <li>Other unlawful or unauthorized processing.</li>
          </ul>
          <p>
            Security measures may include access controls, authentication
            mechanisms, encrypted communications where applicable, authorization
            controls, secure storage practices, monitoring, and other
            appropriate safeguards.
          </p>
          <p>
            However, no electronic system or method of transmission over the
            Internet can be guaranteed to be completely secure. Users are
            responsible for protecting their account credentials and must
            immediately notify FISMPC if they suspect unauthorized access to
            their account.
          </p>
        </div>

        <!-- Section 8 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            8. Retention of Personal Information
          </h2>
          <p>
            FISMPC will retain personal information only for as long as
            reasonably necessary to:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Provide Platform services;</li>
            <li>Maintain account and membership records;</li>
            <li>Process and maintain transaction records;</li>
            <li>Complete verification requirements;</li>
            <li>Fulfill Cooperative operational requirements;</li>
            <li>
              Comply with legal, accounting, regulatory, and reporting
              obligations;
            </li>
            <li>Resolve disputes;</li>
            <li>Prevent fraud and security incidents; or</li>
            <li>Enforce applicable agreements and policies.</li>
          </ul>
          <p>
            When personal information is no longer required, FISMPC will take
            reasonable steps to securely delete, dispose of, anonymize, or
            otherwise appropriately handle the information in accordance with
            applicable laws and retention requirements.
          </p>
          <p>
            Certain information may need to be retained for a longer period
            where required by law or legitimate legal and operational
            requirements.
          </p>
        </div>

        <!-- Section 9 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            9. Account and Data Deletion
          </h2>
          <p>
            Users may request deletion of their FISMPC account and associated
            personal information, subject to applicable legal, regulatory,
            Cooperative, security, and record-retention requirements. Requests
            for account or data deletion may be submitted through the available
            FISMPC support or account-management channels.
          </p>
          <p>
            Before processing a deletion request, FISMPC may need to verify the
            identity of the requesting user. Some information may be retained
            where necessary to:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Comply with legal or regulatory obligations;</li>
            <li>Maintain required financial or transaction records;</li>
            <li>Resolve disputes;</li>
            <li>Prevent fraud;</li>
            <li>Protect the security of the Platform; or</li>
            <li>Establish, exercise, or defend legal claims.</li>
          </ul>
          <p>
            Where information must be retained, FISMPC will limit its use to the
            purposes for which retention is required or permitted.
          </p>
        </div>

        <!-- Section 10 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            10. User Rights
          </h2>
          <p>
            Subject to applicable Philippine laws and regulations, users may
            have rights regarding their personal information, including the
            right to:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>
              Be informed about the processing of their personal information;
            </li>
            <li>Access personal information held by FISMPC;</li>
            <li>Request correction of inaccurate or incomplete information;</li>
            <li>Object to certain processing where applicable;</li>
            <li>Request deletion or blocking where legally permitted;</li>
            <li>
              Withdraw consent where processing is based on consent, subject to
              applicable limitations;
            </li>
            <li>
              Request information regarding the processing of their personal
              information; and
            </li>
            <li>
              Exercise other rights provided under applicable Philippine data
              protection laws.
            </li>
          </ul>
          <p>
            Requests may be subject to identity verification and applicable
            legal limitations.
          </p>
        </div>

        <!-- Section 11 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            11. Children's Privacy
          </h2>
          <p>
            The FISMPC Digital Platform is intended for users who are legally
            eligible to use the services provided by FISMPC.
          </p>
          <p>
            FISMPC does not knowingly collect personal information from children
            where such collection is not permitted by applicable law. If FISMPC
            becomes aware that personal information has been collected from a
            child in circumstances where collection is not permitted,
            appropriate steps may be taken to address the situation.
          </p>
        </div>

        <!-- Section 12 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            12. Cookies and Similar Technologies
          </h2>
          <p>
            The FISMPC mobile application may not use traditional browser
            cookies in the same manner as a website.
          </p>
          <p>
            However, the Platform may use technical mechanisms, local storage,
            authentication tokens, device information, logs, or similar
            technologies necessary to maintain sessions, authenticate users,
            improve security, remember applicable settings, and operate Platform
            services.
          </p>
          <p>
            Where applicable, websites or web-based services associated with
            FISMPC may use cookies or similar technologies according to their
            respective privacy practices.
          </p>
        </div>

        <!-- Section 13 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            13. Third-Party Links and Services
          </h2>
          <p>
            The Platform may contain links or references to websites, payment
            providers, services, or other third-party resources. When a user
            accesses a third-party service, the third party's own privacy policy
            and terms may apply.
          </p>
          <p>
            FISMPC is not responsible for the privacy practices of third-party
            services that are independently operated by other organizations.
            Users are encouraged to review the privacy policies of third-party
            services before providing personal information through those
            services.
          </p>
        </div>

        <!-- Section 14 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            14. Changes to This Privacy Policy
          </h2>
          <p>
            FISMPC may update this Privacy Policy from time to time to reflect:
          </p>
          <ul class="list-disc space-y-1 pl-6">
            <li>Changes to the Platform;</li>
            <li>Changes to Cooperative services;</li>
            <li>Changes in data processing practices;</li>
            <li>Changes in security practices;</li>
            <li>Changes in applicable laws or regulations; or</li>
            <li>Other operational requirements.</li>
          </ul>
          <p>
            When material changes are made, FISMPC may provide notice through
            the Platform, website, or other appropriate communication channels.
            The updated Privacy Policy will indicate the applicable effective or
            last updated date.
          </p>
        </div>

        <!-- Section 15 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            15. Contact Information
          </h2>
          <p>
            For questions, concerns, requests, or complaints regarding this
            Privacy Policy or the processing of personal information, users may
            contact FISMPC through:
          </p>
          <div class="mt-2 rounded-md bg-muted p-4">
            <p class="font-semibold text-foreground">
              FILIPINO INVENTORS SOCIETY MULTI-PURPOSE COOPERATIVE (FISMPC)
            </p>
            <!-- <p><strong>Contact Number:</strong> (02) 1234-5678</p> -->
            <p>
              <strong>Email Address:</strong>
              <a
                href="mailto:info@fisinventorscoop.org"
                class="text-primary hover:underline"
              >
                info@fisinventorscoop.org</a
              >
            </p>
            <p>
              <strong>Address:</strong> Unit 405, 4th Floor, 821 Cortes
              Building, EDSA, South Triangle, Quezon City, Philippines
            </p>
            <p>
              <strong>Website:</strong>
              <a
                href="https://fismulticoop.org/"
                target="_blank"
                class="text-primary hover:underline"
              >
                https://fismulticoop.org/</a
              >
            </p>
          </div>
          <p>
            Users may contact FISMPC regarding requests concerning their
            personal information, account deletion, correction of information,
            privacy concerns, or other data protection matters.
          </p>
        </div>

        <!-- Section 16 -->
        <div class="space-y-4">
          <h2
            class="border-b border-border pb-2 text-xl font-semibold text-foreground"
          >
            16. Acceptance and Acknowledgment
          </h2>
          <p>
            By creating an account, registering for the Platform, submitting
            personal information, or using services provided through the FISMPC
            Digital Platform, you acknowledge that you have read and understood
            this Privacy Policy.
          </p>
          <p>
            Where required, FISMPC will obtain the appropriate consent before
            processing personal information for purposes requiring consent under
            applicable law.
          </p>
          <p>
            This Privacy Policy should be read together with the FISMPC Terms
            and Conditions of Use and other applicable Cooperative policies.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>
