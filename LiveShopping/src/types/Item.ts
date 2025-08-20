export type Item = {
  id_item: number;
  images?: number;
  name_item: string;
  id_seller: number;
  id_category: number;
};

export type ItemSize = {
  id_item_size: number;
  value_size?: string;
  id_size: number;
  id_item: number;
};

export type ItemStock = {
  id_item_stock: number;
  out_item?: number;
  in_item?: string;
  date_move: string;
  id_item_size: number;
};

export type PriceItem = {
  id_price: number;
  price: number;
  date_price: string;
  id_item: number;
};

export type Size = {
  id_size: number;
  name_size: string;
};