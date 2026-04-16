export interface BusinessTrainingType {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
}

export interface BusinessTrainingCategory {
  id: number;
  name: string;
  slug: string;
  description: string;
}

export interface BusinessTrainingTypeDetail extends BusinessTrainingType {
  categories: BusinessTrainingCategory[];
}
