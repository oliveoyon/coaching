# Coaching Management SaaS – Codex Build Guide

## Overview

This document defines how to build the Coaching Center Management SaaS system step by step using Codex.

The goal is:

* Modular development
* No rework
* Clear business logic
* SaaS-ready architecture

---

# Core Business Model

## Roles

* Super Admin (system owner)
* Admin (operational manager)
* Teacher (batch owner)
* Student
* Guardian (optional)

## Ownership Logic

* Each batch has a **teacher-owner**
* Each student belongs to a **batch**
* Teacher owns academic + financial responsibility

## Fee Collection Logic (CRITICAL)

* Fees can be collected by:

  * Admin
  * Teacher

System must store BOTH:

* `owner_teacher_id` → who owns the student/batch
* `collector_id` → who collected the money

👉 This ensures correct:

* due calculation
* reporting
* accountability

---

# SaaS Billing Model

Billing must be configurable per tenant:

* `per_student`
* `per_course`
* `per_batch`

Example:

* One tenant: 8 taka per student/month
* Another: course-based pricing

🚫 Never hardcode billing logic

---

# Communication System

Supports:

* SMS
* WhatsApp
* Email

Triggered by events:

* fee payment
* admission
* due reminder
* attendance alert
* exam notice
* result publish

All controlled by settings.

---

# Development Rules for Codex

* Do NOT build full system at once
* Work module by module
* Always:

  1. Explain understanding
  2. Define schema
  3. Then code
* Keep code modular
* Follow Laravel conventions
* Do not break existing logic

---

# Module Build Order

## Phase 1: Foundation

1. SaaS Tenant System
2. Roles & Permissions
3. Teacher Module
4. Student Module
5. Academic Structure (Class, Subject, Batch)

---

## Phase 2: Core Operations

6. Student Enrollment (batch-wise)
7. Fee Structure & Billing Model
8. Fee Collection (owner vs collector logic)
9. Receipt & Notification Actions
10. Attendance
11. Routine / Schedule

---

## Phase 3: Financial System

12. Expense Module
13. Accounting Module
14. Payroll (optional)

---

## Phase 4: Academic Depth

15. Exam Setup
16. Marks Entry
17. Result Processing
18. Online Exam Foundation

---

## Phase 5: Communication

19. Notice System
20. SMS / WhatsApp / Email Engine
21. WhatsApp Batch Group Module

---

## Phase 6: Reporting & Control

22. Reports Module
23. Audit Log

---

## Phase 7: Frontend

24. Public Website
25. Admin Dashboard UI

---

## Phase 8: Final Review

26. Architecture Review

---

# Key Design Principles

* Student belongs to **batch**
* Batch belongs to **teacher-owner**
* Payment belongs to **teacher-owner**
* Payment collected by **admin or teacher**
* Billing is **configurable**
* Communication is **event-driven**

---

# Critical Fields to Always Track

Every important record must include:

* `tenant_id`
* `owner_teacher_id`
* `created_by`
* `updated_by`

For payments:

* `collector_id`
* `payment_method`
* `fee_period`

---

# Special Features

## WhatsApp Batch Group

* each batch has group
* teacher + admin are admins
* store group link
* export student contacts

---

# Reporting Requirements

## Financial

* daily collection
* monthly income
* expense report
* due report
* teacher-wise income
* collector-wise collection

## Academic

* attendance report
* exam result
* weak students

---

# Codex Prompting Strategy

## Always use this format:

Context → Task → Rules

Example:

Context:
This is a SaaS coaching system with teacher-owner model.

Task:
Implement fee collection module.

Rules:

* Do not break existing logic
* Maintain owner vs collector logic
* Keep it modular

---

# Final Notes

* Think like a system architect
* Build step by step
* Validate each module before moving next
* Keep everything SaaS-ready

---

# End of Document
