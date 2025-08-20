export type Promotion = {
  id_promotion: number;
  name_promotion: string;
  description?: string;
  percentage: number;
  start_date: string;
  end_date?: string;
  id_item: number;
};