namespace ApiRecord {
    type Record = Record<string, any>;

    interface Range {
        min: number
        max: number
        toString: string
    }

    interface Entity extends ApiRecord.Record {
        '@id': string
    }

    interface HydraCollection<T> extends Record {
        'hydra:member': Array<T>
        'hydra:totalItems': number
        'hydra:view': {
            'hydra:first'?: string
            'hydra:last'?: string
            'hydra:next'?: string
        }
    }

    // Entity traits
    interface EntityGroupedByGeneration {
        generation: string
    }

    interface EntityGroupedByVersionGroup {
        versionGroup: string
    }

    interface EntityGroupedByVersion {
        version: string
    }

    interface EntityHasDefault {
        isDefault: boolean
    }

    interface EntityHasDescription {
        shortDescription?: string,
        description: string,
    }

    interface EntityHasFlavorText {
        flavorText?: string,
    }

    interface EntityHasIcon {
        icon?: string
    }

    interface EntityHasId {
        id: number
    }

    interface EntityHasName {
        name: string
    }

    interface EntityHasSlug {
        slug: string
    }

    interface EntityHasNameAndSlug extends EntityHasName, EntityHasSlug {
    }

    interface EntityIsSortable {
        position: number
    }

    interface MediaEntity {
        url?: string
    }

    interface Version extends ApiRecord.Entity,
        EntityGroupedByVersionGroup,
        EntityHasId,
        EntityHasNameAndSlug,
        EntityIsSortable {
        featureSlugs: Array<string>
        generationNumber: number
    }

    interface TimeOfDay extends ApiRecord.Entity,
        EntityHasId,
        EntityIsSortable,
        EntityHasNameAndSlug {
        startsIso8601: string
        endsIso8601: string
    }
}
