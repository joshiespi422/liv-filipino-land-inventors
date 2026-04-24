export interface IntellectualPropertyClaim {
  id: number;
  description: string;
}

export interface IntellectualPropertyDocument {
  id: number;
  attachment: string;
}

export interface IntellectualProperty {
  id: number;
  status_name: IntellectualPropertyStatus;
  user_name: string;
  creation_type: IntellectualPropertyCreationType;
  form_type: IntellectualPropertyFormType;
  title: string;
}

export interface IntellectualPropertyDetail extends IntellectualProperty {
  description: string;
  applicability: string;
  claims: IntellectualPropertyClaim[];
  documents: IntellectualPropertyDocument[];
}

// filter usage
export type IntellectualPropertyStatus =
  | 'pending'
  | 'registered'
  | 'rejected'
  | 'expired'
  | 'waiting_for_payment';

export type IntellectualPropertyFormType = 'payment' | 'grant';

export type IntellectualPropertyCreationType = 'business_idea' | 'invention';
