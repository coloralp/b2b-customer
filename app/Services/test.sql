select `games`.*,
       (select sum(`keys`.`cost_convert_euro`)
        from `keys`
        where `games`.`id` = `keys`.`game_id`
          and `status` = 1
          and `keys`.`deleted_at` is null) as `active_keys_sum_cost_convert_euro`
from `games`
where `stock` >= 1
  and `games`.`deleted_at` is null
order by `active_keys_sum_cost_convert_euro` asc
