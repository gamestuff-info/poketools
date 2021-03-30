namespace ApiRecord {
    namespace Ability {
        interface AbilityInVersionGroup extends ApiRecord.Entity,
            EntityGroupedByVersionGroup,
            EntityHasDescription,
            EntityHasFlavorText,
            EntityHasId,
            EntityHasNameAndSlug {
        }
    }
}
