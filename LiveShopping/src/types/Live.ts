export type Live = {
  id_live: number;
  start_live: string;
  end_live?: string;
  nbr_like?: number;
  id_seller: number;
};

export type LiveDetail = {
  id_live_detail: number;
  id_item: number;
  id_live: number;
};