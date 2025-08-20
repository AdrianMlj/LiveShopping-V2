export type Favorite = {
  id_favorites: number;
  create_at: string;
  id_client: number;
};

export type FavoriteDetail = {
  id_favorite_detail: number;
  id_item_size: number;
  id_favorites: number;
};