export interface GlobalSetting {
  id: number;
  default_amount: string;
  default_interest_rate: string;
  default_term_months: number;
}

export interface LoanAssistance {
  id: number;
  status_name: LoanStatus;
  user_name: string;
  amount: number;
  interest_rate: number;
  term_months: number;
  start_date: string;
  end_date: string;

  // Display-ready fields from the resource
  interest_rate_display: string;
  start_date_display: string;
  end_date_display: string;
}

export interface LoanSchedule {
  id: number;
  status_name: ScheduleStatus;
  total_payment: number;
  due_date: string;

  // Display-ready fields from the resource
  due_date_display: string;
}

// filter usage
export type LoanStatus =
  | 'pending'
  | 'active'
  | 'rejected'
  | 'cancelled'
  | 'finished';

export type ScheduleStatus = 'paid' | 'unpaid' | 'overdue';
