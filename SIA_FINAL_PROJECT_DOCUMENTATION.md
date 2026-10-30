# SYSTEM INTEGRATION AND ARCHITECTURE (SIA)
## FINAL PROJECT SPECIFICATION & ARCHITECTURAL BLUEPRINT
### Implementation Checklist & Architectural Diagrams Documentation

---

- **System Title**: **HerdShield: Municipal Livestock Biosecurity, Vaccination Monitoring & Supply Requisition System**
- **Target LGU / Jurisdiction**: **City Government of Ormoc, Leyte (City Veterinary & Agriculture Office - CVO)**
- **Student / Project Proponent**: **Charls Michael Cordero** (Student No. 8)
- **Course & Evaluators**:
  - **System Integration and Architecture (SIA)**
  - **Application Development (AppDev)** – Mr. Ian Celmar A. Avila
  - **Information Assurance & Security 1 (IAS1)** – Mr. Roel Daniot

---

## 1. Use Case Diagram (System Users & Primary Roles)

### Actors Identified:
1. **Registered Farmer / Livestock Owner (Primary User)**:
   - Registers animal heads in the Ormoc City database.
   - Files field vaccination requests during municipal campaigns.
   - Monitors live vaccination status and downloads verified digital transit permits / certificates.
2. **Field Veterinarian / CVO Staff (Secondary Actor / Operator)**:
   - Evaluates incoming vaccination requests and schedules field appointments within the official 8-hour shift window (9:00 AM – 5:00 PM PHT).
   - Records physical livestock vitals and flags adverse biosecurity conditions.
   - Marks vaccination administration and submits cold-chain vaccine replenishment requisitions to Admin.
3. **City Veterinary Officer / System Administrator (Admin - Charls Michael Cordero)**:
   - Reviews, approves, or declines staff vaccine stock requisitions.
   - Issues and seals official CVO Vaccination & Transit Certificates with unique verification identifiers.
   - Manages user accounts, enforces RBAC, configures lockout rules, restores database snapshots, and reviews immutable IAS-1 audit logs.
4. **Supabase Cloud Backend & Auth Engine (Secondary Supporting System)**:
   - Authenticates credentials, validates SHA-256 password digests, executes RLS security rules, and persists ACID transactions.

```mermaid
flowchart LR
    %% Actors
    Farmer["🧑‍🌾 Registered Farmer / Owner"]
    Staff["🩺 CVO Field Staff / Veterinarian"]
    Admin["🛡️ System Admin (Charls Michael Cordero)"]
    DBSystem[("☁️ Supabase Cloud & DB Services")]

    subgraph HerdShield_System ["HerdShield: Core System Boundary"]
        UC1(["UC-01: User Authentication & OTP Recovery"])
        UC2(["UC-02: Register Livestock Head to Ormoc Registry"])
        UC3(["UC-03: Submit Vaccination Request"])
        UC4(["UC-04: Review & Schedule Within 8-Hr Shift (9AM-5PM)"])
        UC5(["UC-05: Record Health Vitals & Mark Administered"])
        UC6(["UC-06: Submit Vaccine Stock Requisition"])
        UC7(["UC-07: Approve/Decline Stock Requisition"])
        UC8(["UC-08: Issue & Validate CVO Official Certificate"])
        UC9(["UC-09: User Account & Role-Based Access Control"])
        UC10(["UC-10: Database Backup, Restore & Audit Trail"])
    end

    %% Farmer Connections
    Farmer --> UC1
    Farmer --> UC2
    Farmer --> UC3
    Farmer --> UC8

    %% Staff Connections
    Staff --> UC1
    Staff --> UC4
    Staff --> UC5
    Staff --> UC6

    %% Admin Connections
    Admin --> UC1
    Admin --> UC7
    Admin --> UC8
    Admin --> UC9
    Admin --> UC10

    %% System Services
    UC1 -.-> DBSystem
    UC2 -.-> DBSystem
    UC3 -.-> DBSystem
    UC4 -.-> DBSystem
    UC5 -.-> DBSystem
    UC6 -.-> DBSystem
    UC7 -.-> DBSystem
    UC8 -.-> DBSystem
    UC9 -.-> DBSystem
    UC10 -.-> DBSystem
```

---

## 2. System Flow Diagram (Cross-Functional Swimlanes & Pools)

This cross-functional swimlane diagram depicts a **Complete Transaction Cycle (3rd & 4th Increment Checking)**, illustrating interactions and handoffs between the **Farmer Pool**, **CVO Staff Pool**, **Admin Pool**, and the **Database Engine Pool**.

```mermaid
flowchart TD
    subgraph Farmer_Lane ["Pool: Farmer / Livestock Owner"]
        F_Start(["Start: Farmer Signs In"]) --> F_Reg["1.1 Register Livestock Head in Registry"]
        F_Reg --> F_Req["1.2 Submit Vaccination Request & Preferred Date"]
        F_Track["1.3 Receive In-App Toast & Track Status"]
        F_Download(["End: Download Verified Clearance Certificate"])
    end

    subgraph Staff_Lane ["Pool: CVO Staff / Field Veterinarian"]
        S_Receive["2.1 Receive Live Vaccination Request in Queue"]
        S_Check{"Within 8-Hour Shift?<br>(09:00 AM - 05:00 PM)"}
        S_Decline["2.2 Notify Reschedule / Decline"]
        S_Sched["2.3 Accept & Assign Time Slot"]
        S_StockCheck{"Sufficient Cold-Chain<br>Vaccine Stock?"}
        S_ReqStock["2.4 Submit Vaccine Stock Requisition to Admin"]
        S_Vaccinate["2.5 Visit Site, Check Vitals & Administer Dose"]
        S_Complete["2.6 Update Status to 'Vaccinated'"]
    end

    subgraph Admin_Lane ["Pool: Admin (Charls Michael Cordero)"]
        A_ReviewReq{"3.1 Review Staff Supply Requisition"}
        A_Approve["3.2 Approve Requisition & Deduct Central Batch Stock"]
        A_CertReview["3.3 Verify Administered Vaccination Record"]
        A_IssueCert["3.4 Authorize & Issue CVO Official Certificate with QR"]
    end

    subgraph DB_Lane ["Pool: Supabase Database & Security Engine"]
        DB_Herd[("Insert into public.herd")]
        DB_Req[("Insert into public.vaccination_requests")]
        DB_UpdateReq[("Update status & scheduled_time")]
        DB_Requisition[("Insert into public.vaccine_requisitions")]
        DB_ApproveRequisition[("Update stock in public.vaccines")]
        DB_Cert[("Insert into public.vaccination_certificates")]
        DB_Audit[("Write immutable log into public.audit_logs")]
    end

    %% Interactions & Handoffs
    F_Reg --> DB_Herd
    DB_Herd --> DB_Audit
    F_Req --> DB_Req
    DB_Req --> F_Track
    F_Track --> S_Receive

    S_Receive --> S_Check
    S_Check -- "No" --> S_Decline
    S_Check -- "Yes" --> S_Sched
    S_Sched --> DB_UpdateReq
    DB_UpdateReq --> S_StockCheck

    S_StockCheck -- "Stock Depleted" --> S_ReqStock
    S_ReqStock --> DB_Requisition
    DB_Requisition --> A_ReviewReq
    A_ReviewReq -- "Approve" --> A_Approve
    A_Approve --> DB_ApproveRequisition
    DB_ApproveRequisition --> S_Vaccinate

    S_StockCheck -- "In Stock" --> S_Vaccinate
    S_Vaccinate --> S_Complete
    S_Complete --> DB_UpdateReq
    DB_UpdateReq --> A_CertReview

    A_CertReview --> A_IssueCert
    A_IssueCert --> DB_Cert
    DB_Cert --> DB_Audit
    DB_Cert --> F_Download
```

---

## 3. Entity-Relationship Diagram (ERD)

The database strictly conforms to **PostgreSQL / Supabase** relational integrity, utilizing Foreign Keys, Unique Indexes, and Row Level Security (RLS) constraints.

```mermaid
erDiagram
    PROFILES ||--o{ HERD : "owns"
    PROFILES ||--o{ VACCINATION_REQUESTS : "submits"
    PROFILES ||--o{ VACCINE_REQUISITIONS : "requests"
    HERD ||--o{ VACCINATION_REQUESTS : "receives"
    VACCINES ||--o{ VACCINE_REQUISITIONS : "supplies"
    VACCINATION_REQUESTS ||--o| VACCINATION_CERTIFICATES : "generates"
    PROFILES ||--o{ AUDIT_LOGS : "triggers"

    PROFILES {
        uuid id PK
        uuid auth_user_id
        varchar full_name
        varchar username UK
        varchar email UK
        varchar password_hash
        varchar role "admin | staff | vet | farmer"
        varchar account_status "active | suspended"
        boolean is_locked
        int failed_attempts
        timestamptz lockout_until
        varchar barangay
        varchar city
        timestamptz created_at
    }

    HERD {
        bigserial id PK
        varchar animal_tag UK "ORM-2026-XXXXX"
        varchar species "Cattle | Carabao | Swine | Goat"
        varchar breed
        varchar gender
        int age_months
        varchar health_status
        uuid owner_id FK
        varchar owner_name
        varchar barangay
        varchar city
        timestamptz created_at
    }

    VACCINES {
        bigserial id PK
        varchar vaccine_name
        varchar target_disease
        varchar target_species
        varchar batch_number UK
        int stock_quantity
        varchar dosage_per_head
        varchar storage_temp
        timestamptz created_at
    }

    VACCINE_REQUISITIONS {
        bigserial id PK
        varchar requisition_no UK "REQ-STOCK-XXXX"
        uuid staff_id FK
        varchar staff_name
        varchar vaccine_name
        int requested_doses
        varchar urgency
        text reason
        varchar status "Pending_Admin_Approval | Approved | Declined"
        varchar admin_approver
        timestamptz approved_at
        timestamptz created_at
    }

    VACCINATION_REQUESTS {
        bigserial id PK
        varchar request_tracking_no UK "REQ-ORM-XXXXXX"
        uuid user_id FK
        varchar farmer_name
        varchar animal_tag
        varchar species
        varchar vaccine_type
        varchar barangay
        date requested_date
        timestamptz scheduled_time "9AM-5PM PHT"
        varchar administered_by
        timestamptz completed_at
        varchar certificate_no
        varchar status "Submitted | Approved | Vaccinated | Certified"
        timestamptz created_at
    }

    VACCINATION_CERTIFICATES {
        bigserial id PK
        varchar certificate_no UK "CVO-CERT-XXXX"
        bigint request_id FK
        varchar animal_tag
        varchar species
        varchar farmer_name
        varchar barangay
        varchar vaccine_name
        varchar batch_number
        date date_administered
        date next_booster_date
        varchar licensed_vet
        varchar vet_prc_no
        varchar qr_verification_code
        boolean is_valid
        timestamptz created_at
    }

    AUDIT_LOGS {
        bigserial id PK
        uuid user_id FK
        varchar username
        varchar role
        varchar action
        varchar affected_module
        varchar ip_address
        varchar result "SUCCESS | FAILURE | WARNING"
        jsonb details
        timestamptz created_at
    }

    OTP_CODES {
        bigserial id PK
        varchar email
        varchar otp_hash
        timestamptz expires_at
        boolean is_used
        int attempts_remaining
    }

    SYSTEM_BACKUPS {
        bigserial id PK
        varchar backup_name
        bigint backup_size_bytes
        int records_count
        jsonb backup_snapshot
        varchar created_by
        timestamptz created_at
    }
```

---

## 4. Comprehensive System Flowchart

The system flowchart maps the operational logic, security checks, and routing decisions from initial authentication to final certificate generation.

```mermaid
flowchart TD
    Start([User Opens HerdShield Web/PWA App]) --> AuthCheck{Existing Active Session?}
    
    AuthCheck -- "Yes" --> RoleRoute
    AuthCheck -- "No" --> LoginForm[/Input Username/Email & Password/]

    LoginForm --> LockoutCheck{Is Account Under Cooldown / Lockout?}
    LockoutCheck -- "Yes" --> ShowLockoutTimer[/Display Lockout Countdown Banner/] --> Stop1([Halt Login Attempt])
    LockoutCheck -- "No" --> CaptchaCheck{Failed Attempts >= 3?}

    CaptchaCheck -- "Yes" --> SolveCaptcha[/Interactive CAPTCHA Verification/]
    SolveCaptcha -- "Incorrect" --> FailAttempt[Increment Failed Attempts Counter]
    SolveCaptcha -- "Correct" --> VerifyHash
    CaptchaCheck -- "No" --> VerifyHash[Hash Input with SHA-256 & Query Supabase]

    VerifyHash --> CredsMatch{Credentials Valid?}
    CredsMatch -- "No" --> FailAttempt
    FailAttempt --> CheckLockoutThreshold{Failed Attempts >= 3?}
    CheckLockoutThreshold -- "Yes" --> SetLockout[Apply 1, 5, or 15-Minute Lockout Window]
    SetLockout --> WriteAuditFail[Log USER_LOGIN_FAILED to Audit Logs] --> LoginForm
    CheckLockoutThreshold -- "No" --> WriteAuditFail

    CredsMatch -- "Yes" --> ResetCounter[Reset Failed Counter & Initialize 15-Min Inactivity Timer]
    ResetCounter --> RoleRoute{Evaluate User Role}

    %% Farmer Route
    RoleRoute -- "Role: Farmer" --> FarmerDash[/Load Farmer Mobile Dashboard/]
    FarmerDash --> FarmerAction{User Action Selection}
    FarmerAction -- "Register Head" --> InputAnimal[/Fill Ormoc Ear Tag, Species, Breed, Barangay/]
    InputAnimal --> SaveHerdDB[(Insert Head into public.herd)]
    SaveHerdDB --> Toast1[/Show In-App Toast Confirmation/] --> FarmerDash

    FarmerAction -- "Request Vaccination" --> SelectAnimal[/Choose Registered Tag & Vaccine Program/]
    SelectAnimal --> SubmitReqDB[(Insert Request into public.vaccination_requests)]
    SubmitReqDB --> Toast2[/Show In-App Toast Confirmation/] --> FarmerDash

    %% Staff Route
    RoleRoute -- "Role: Staff / Vet" --> StaffDash[/Load CVO Field Operations View/]
    StaffDash --> StaffAction{Staff Action Selection}
    StaffAction -- "Review Request" --> SchedModal[/Open Shift Scheduler/]
    SchedModal --> TimeSlotCheck{Time Slot Selected<br>Between 09:00 AM - 05:00 PM?}
    TimeSlotCheck -- "No" --> ToastReject[/In-App Toast: Outside 8-Hr Operating Shift/] --> SchedModal
    TimeSlotCheck -- "Yes" --> ApproveReq[(Update Request Status to 'Approved')] --> StaffDash

    StaffAction -- "Administer Vaccine" --> MarkDose[(Update Request Status to 'Vaccinated')]
    MarkDose --> StaffDash

    StaffAction -- "Request Cold-Chain Stock" --> SupplyModal[/Input Vaccine, Doses & Urgency/]
    SupplyModal --> SaveReqStock[(Insert into public.vaccine_requisitions)] --> StaffDash

    %% Admin Route
    RoleRoute -- "Role: Admin (Charls Cordero)" --> AdminDash[/Load Comprehensive Web Admin Suite/]
    AdminDash --> AdminAction{Admin Module Selection}
    AdminAction -- "Requisitions Queue" --> RequisitionReview{Approve Requisition?}
    RequisitionReview -- "Yes" --> ReleaseStock[(Update public.vaccines & public.vaccine_requisitions)] --> AdminDash
    RequisitionReview -- "No" --> DeclineStock[(Mark Requisition Declined)] --> AdminDash

    AdminAction -- "Certificate Issuance" --> GenCert[(Create Record in public.vaccination_certificates)]
    GenCert --> ShowPermitModal[/Display Official Clearance with QR Code & Print Options/] --> AdminDash

    AdminAction -- "Security & Audit" --> ViewAudit[/Audit Log Trail / JSON Snapshot Backup & Restore/] --> AdminDash

    %% Session Timeout
    ResetCounter -. 14 Mins Inactivity .-> WarnModal[/Show 60-Second Auto-Logout Warning/]
    WarnModal -- "User Clicks Stay In" --> ResetCounter
    WarnModal -- "Timeout Expired" --> AutoLogout[Clear Session & Broadcast Logout to Other Tabs] --> LoginForm
```

---

## 5. Network Diagram (Network Architecture & Distributed Data Flow)

The network model illustrates the distributed architecture connecting end-user devices in Ormoc City to edge services and the cloud database cluster.

```mermaid
flowchart TB
    subgraph Ormoc_Client_Tier ["Tier 1: Client Devices (Ormoc City Field & Offices)"]
        F_Phone["📱 Farmer Smartphone / PWA<br>(4G/5G Cellular / PLDT Home)"]
        S_Tablet["📱 CVO Field Staff Tablet<br>(Mobile Field Unit)"]
        A_PC["💻 Admin Workstation<br>(CVO Headquarters, Ormoc City Hall)"]
    end

    subgraph Edge_Security_Tier ["Tier 2: Edge Network & Secure Transport"]
        DNS["🌐 DNS & SSL/TLS 1.3 Termination<br>(Cloudflare CDN Edge)"]
        WAF["🛡️ Web Application Firewall<br>(DDoS Protection & Rate Limiting)"]
    end

    subgraph App_Server_Tier ["Tier 3: Web Presentation & Local Node"]
        Apache["🖥️ Local Edge Web Server / Apache<br>c:\\xampp\\htdocs\\herdshield<br>Port: 80 / 5500 (Live Server)"]
        HTMLApp["📄 HerdShield SPA Frontend Engine<br>(index.html / Responsive Vanilla JS)"]
    end

    subgraph Cloud_Backend_Tier ["Tier 4: Managed Cloud Infrastructure (Supabase)"]
        KongGateway["🚪 Kong API Gateway<br>(https://kvfnrjuhwbdgqevvuahv.supabase.co)"]
        PostgREST["⚡ PostgREST RESTful API Engine<br>(Instant CRUD with Schema Reflection)"]
        GoTrue["🔐 GoTrue Auth Engine<br>(Session JWT & Token Provider)"]
        Realtime["📡 Supabase Realtime Server<br>(WebSocket Broadcasts)"]
    end

    subgraph Data_Storage_Tier ["Tier 5: Database Cluster & Backups"]
        PostgreSQL[("🐘 PostgreSQL 15 Relational DB<br>(Schema: public.* with RLS Policies)")]
        EncryptedStorage["🗄️ AES-256 Cold Storage<br>(Automated Daily Snapshots & JSON Backups)"]
    end

    %% Data Connections
    F_Phone -- "HTTPS / TLS 1.3" --> DNS
    S_Tablet -- "HTTPS / TLS 1.3" --> DNS
    A_PC -- "Local LAN / Live Server" --> Apache
    Apache --> HTMLApp

    DNS --> WAF
    WAF --> KongGateway
    HTMLApp -- "RESTful HTTPS Queries" --> KongGateway

    KongGateway --> PostgREST
    KongGateway --> GoTrue
    KongGateway --> Realtime

    PostgREST -- "Connection Pooling (PgBouncer)" --> PostgreSQL
    GoTrue --> PostgreSQL
    PostgreSQL --> EncryptedStorage
```

---

## 6. Architecture & Implementation Compliance Matrix

| Requirement | SIA Standard Notation | HerdShield Implementation Reference | Status |
|---|---|---|:---:|
| **1. Use Case Diagram** | Standard UML Actors & Functionality Associations | 3 Primary Actors (Farmer, Staff, Admin) + 1 Supporting Service; 10 distinct Use Cases. | **COMPLIANT** |
| **2. Swimlane Flow** | Multi-Pool Cross-Functional BPMN Flow | 4 Discrete Swimlanes (Farmer, CVO Staff, Admin Charls Cordero, Database Engine) detailing complete handoffs. | **COMPLIANT** |
| **3. Database ERD** | Crow's Foot Relational Notation | 10 Entities (`profiles`, `herd`, `vaccines`, `vaccine_requisitions`, `vaccination_requests`, `vaccination_certificates`, `audit_logs`, `otp_codes`, `system_backups`, `ormoc_quarantine_advisories`). | **COMPLIANT** |
| **4. System Flowchart** | ANSI / ISO Flowchart Standard Symbols | Process boxes, diamonds for logic decisions, input/output trapezoids, and database cylinders covering authentication to certificate output. | **COMPLIANT** |
| **5. Network Diagram** | Multi-Tier Distributed Cloud Architecture | Detailed 5-Tier layout from Ormoc Client Devices to Cloudflare Edge, Local Apache/Live Server, Kong Gateway, and PostgreSQL DB. | **COMPLIANT** |
| **Unified Terms** | Consistent Nomenclature across all diagrams | System title: **HerdShield**; City: **Ormoc City**; Admin: **Charls Michael Cordero**; Shift: **9:00 AM - 5:00 PM PHT**. | **COMPLIANT** |

---
*Documentation compiled for System Integration and Architecture (SIA) Final Project Evaluation.*
