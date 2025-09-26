export type Notification = {
  id_notification: number;
  title: string;
  content: string;
  is_read?: boolean;
  date_creation: string;
  id_type: number;
  id_user: number;
};

export type LiaisonNotification = {
  id_liaison: number;
  name_table: string;
  id_table: number;
  id_notification: number;
};

export type NotificationType = {
  id_type: number;
  name_type: string;
};