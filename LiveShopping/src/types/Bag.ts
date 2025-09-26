export type Bag = {
  id_bag: number;
  create_at: string;
  is_commande: boolean;
  id_client: number;
  id_seller: number;
};

export type BagDetail = {
  id_bag_detail: number;
  id_item_size: number;
  id_bag: number;
};