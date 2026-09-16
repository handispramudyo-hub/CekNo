export type RiskLevel = 'low' | 'caution' | 'risky' | 'high'

export interface RiskAssessment {
  id: number
  phone_number_id: number
  final_score: number | null
  risk_level: RiskLevel | null
  community_score: number | null
  rule_score: number | null
  xgboost_probability: number | null
  indobert_probability: number | null
  explanation: string | null
  factors: Record<string, unknown> | string | null
  model_version: string | null
  assessed_at: string | null
  created_at: string | null
}

export interface Tag {
  id: number
  name: string
  slug?: string
}

export interface TagChip {
  id: number
  tag: Tag
  must_approve: boolean
  status: string
  created_at?: string
}

export interface Report {
  id: number
  phone_number_id: number
  user_id: number | null
  category_id: number | null
  category?: Category
  description: string
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  user?: { id: number; name: string }
}

export interface Review {
  id: number
  phone_number_id: number
  user_id: number | null
  rating: number
  comment: string | null
  sentiment?: string | null
  fraud_probability?: number | null
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  user?: { id: number; name: string }
}

export interface PhoneNumber {
  id: number
  phone_number: string
  normalized_number: string
  country_code: string
  search_count: number
  status: 'active' | 'hidden' | 'suspended'
  risk_level?: RiskLevel | null
  created_at?: string
  updated_at?: string
}

export interface Category {
  id: number
  name: string
  slug: string
  description?: string | null
  is_active: boolean
}

export interface User {
  id: number
  name: string
  email: string
  phone?: string | null
  email_verified_at?: string | null
  is_active: boolean
  status?: 'active' | 'suspended'
  role: 'user' | 'moderator' | 'admin'
  created_at?: string
  phone_numbers?: number
  reports_count?: number
  reviews_count?: number
}

export interface SearchHistoryItem {
  id: number
  phone_number?: unknown
  created_at?: string
  searched_at?: string
}

export interface NumberDetail extends PhoneNumber {
  risk_assessment?: RiskAssessment | null
  tags?: TagChip[]
  reports?: Report[]
  reviews?: Review[]
  latest_risk_assessment?: RiskAssessment | null
}

export interface DashboardData {
  pending_reports: number
  pending_reviews: number
  pending_tags: number
  new_users: number
  total_numbers: number
  recent_moderation: ModerationLog[]
}

export interface AuditLog {
  id: number
  user_id: number | null
  user?: { name: string }
  action: string
  auditable_type: string | null
  auditable_id: number | null
  ip_address: string | null
  user_agent: string | null
  metadata: Record<string, unknown> | null
  created_at: string
}

export interface ModerationLog {
  id: number
  admin_id?: number | null
  admin?: { name: string }
  target_type?: string
  target_id?: number
  action: string
  reason?: string | null
  created_at?: string
}

export interface MlModelInfo {
  id: number
  name: string
  model_type: string
  version: string
  status: 'active' | 'inactive' | 'archived'
  metrics: Record<string, unknown> | null
  updated_at?: string
}

export interface ContactContribution {
  id: number
  phone_number_id?: number
  label: string
  category?: string
  consent_version?: string
  status: 'pending' | 'approved' | 'rejected' | 'withdrawn'
  created_at?: string
  phone_number?: { id: number; normalized_number: string }
}

export interface ContributionSyncResult {
  created: number
  duplicates: number
  errors: Array<{ phone: string; message: string }>
}

export interface ContributionsResponse {
  consent?: { id: number; consent_version: string; scope: string } | null
  consent_version?: string
  contributions: { data: ContactContribution[]; total?: number }
}