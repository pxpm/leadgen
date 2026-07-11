# Lead Intake Assistant - AI Conversation & Extraction Specification

## Purpose

This document defines how AI conversations work within Lead Intake Assistant.

It is the source of truth for:

- Conversation orchestration
- Information extraction
- Structured data collection
- Lead qualification completion
- Summary generation
- AI provider integration

This document should be followed by all AI implementations.

---

# Core Philosophy

The user should feel like they are having a conversation with a knowledgeable assistant.

The user should never feel like they are filling out a form.

The AI's objective is to:

1. Understand the customer's situation
2. Collect required information naturally
3. Adapt questions dynamically
4. Minimize friction
5. Produce structured output

---

# Separation of Responsibilities

## AI Responsibilities

The AI is responsible for:

- Asking questions
- Understanding answers
- Determining follow-up questions
- Adapting wording
- Maintaining conversational flow
- Extracting structured fields
- Generating summaries

---

## Platform Responsibilities

The platform is responsible for:

- Required fields
- Validation
- Completion rules
- Tenant configuration
- Industry configuration
- Lead status

The AI must never decide whether a lead is complete.

---

# Conversation Lifecycle

## Step 1

Conversation starts.

AI greets user.

Example:

"Hi! I'd be happy to help with your roofing project. Can you briefly describe what you need?"

---

## Step 2

AI identifies intent.

Examples:

- Repair
- Replacement
- Inspection
- Leak
- Emergency

Intent is stored as structured data.

---

## Step 3

AI collects required information.

Questions should be conversational.

Never ask multiple complex questions at once.

---

## Step 4

AI continuously extracts structured fields.

After every message:

- Parse information
- Update qualification state
- Check missing fields

---

## Step 5

Qualification completes only when required fields are collected.

---

## Step 6

Summary is generated.

Lead is delivered.

---

# Conversation Rules

## Rule 1

Prefer natural dialogue.

Avoid:

"What is your roof type?"

Prefer:

"Do you know what type of roof you currently have? For example tile, slate, metal, or another material?"

---

## Rule 2

Ask only one primary question at a time.

---

## Rule 3

Do not repeat information already collected.

---

## Rule 4

If a user provides multiple pieces of information, extract all of them.

Example:

"I have a leaking tile roof in Lisbon."

Extract:

- problem_type
- roof_type
- address/location

---

## Rule 5

Always prioritize missing required fields.

---

# Structured Data Model

Every industry defines:

- Required fields
- Optional fields
- Rules

---

## Example Roofing Schema

Required:

- contact_name
- phone
- address
- problem_type
- roof_type

Optional:

- urgency
- insurance_claim
- roof_age
- leak_location
- roof_size

---

# Extraction Engine

After every user message:

1. Send message to extraction layer
2. Identify structured fields
3. Normalize values
4. Store confidence scores

---

## Example

User:

"My asbestos roof is leaking near the chimney."

Extract:

{
  "roof_type": "asbestos",
  "problem_type": "repair",
  "leak_location": "chimney"
}

Confidence values should also be stored.

---

# Required Field Tracking

The platform maintains:

Collected fields
Missing fields

Example:

Collected:

- problem_type
- roof_type

Missing:

- address
- phone
- contact_name

The AI receives this information with every request.

---

# AI Prompt Context

Each conversation request should include:

## Tenant Information

- Company name
- Industry
- Branding

---

## Qualification State

Collected fields

Missing fields

Current lead status

---

## Industry Rules

Industry-specific requirements

---

## Conversation History

Relevant message history

Avoid sending unnecessary history.

---

# Prompt Structure

System Prompt

Industry Prompt

Qualification State

Conversation History

Latest User Message

---

# Industry Configuration Format

Example:

industry: roofing

required_fields:
  - contact_name
  - phone
  - address
  - roof_type
  - problem_type

optional_fields:
  - roof_age
  - urgency
  - insurance_claim

rules:
  - replacement requires roof_size
  - asbestos requires removal_details
  - repair requires leak_location

---

# Dynamic Questioning

The AI should determine the next question based on:

- Missing required fields
- User intent
- Industry rules

Example:

User:

"I want to replace my roof."

Platform:

problem_type = replacement

Missing:

- roof_type
- address
- contact_name
- phone

AI:

"Do you know what type of roof you currently have?"

---

# Tool / Function Calling

The AI should never store data directly.

Instead:

AI calls structured tools.

Examples:

update_field()

mark_field_confidence()

request_file_upload()

generate_summary()

The platform owns persistence.

---

# File Upload Strategy

The AI can request:

- Photos
- PDFs
- Documents

Example:

"Could you upload a few photos of the affected area? That will help us understand the situation."

The AI should know when photos are beneficial.

---

# Completion Logic

Qualification is complete when:

1. All required fields are present
2. Validation passes
3. Contact information exists

Only the platform may mark completion.

---

# Summary Generation

Once qualification completes:

Generate:

## Executive Summary

Short overview.

---

## Structured Summary

All captured fields.

---

## Missing Information

Any optional information not provided.

---

## Recommended Follow-Up

Suggested next actions for contractor.

---

# Lead Scoring

Initial version should be rule-based.

Signals:

- Photos uploaded
- Project type
- Urgency
- Information completeness

Output:

1-10 score

---

# Hallucination Prevention

AI must:

- Never invent customer information
- Never assume values
- Never fabricate phone numbers
- Never fabricate addresses

Unknown values must remain null.

---

# Cost Optimization

Use smaller models for:

- Extraction
- Classification

Use larger models for:

- Summaries
- Complex conversations

---

# Conversation Memory Strategy

Persist:

- Structured fields
- Qualification state
- Important messages

Avoid sending entire histories when unnecessary.

Use summarized memory when conversations become large.

---

# Error Handling

If extraction confidence is low:

Ask a clarification question.

Example:

"I'm not completely sure I understood. Are you looking to repair the roof or replace it?"

---

# AI Provider Abstraction

Support:

- OpenAI
- Anthropic

Provider should be swappable.

The application must not depend on provider-specific logic.

---

# Future Enhancements

- Voice intake
- WhatsApp qualification
- Multilingual qualification
- Industry-specific AI agents
- Visual image analysis
- Automated estimate preparation

These are future features and should not affect MVP implementation.

---

# Success Criteria

The AI should:

- Feel conversational
- Minimize user effort
- Capture required information
- Produce structured output
- Generate actionable summaries

If the AI creates a pleasant conversation but fails to collect required information, it has failed.

If the AI collects all required information but the experience feels like a rigid form, it has also failed.

The goal is natural conversation with guaranteed structured outcomes.
