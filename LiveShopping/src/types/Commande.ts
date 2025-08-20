export type StateCommande = {
  id_state: number;
  name_state: string;
};

export type Commande = {
  id_commande: number;
  id_state: number;
  id_bag: number;
};