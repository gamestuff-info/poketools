import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';
import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import PktMarkdown from '../common/components/PktMarkdown';

export default function AbilityTeaser(props: { ability: ApiRecord.Ability.AbilityInVersionGroup }) {
    const {ability} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <Teaser label={ability.name}
                href={generatePath(Routes.ABILITY_VIEW, {version: currentVersion.slug, ability: ability.slug})}
                description={<PktMarkdown>{ability.shortDescription ?? ''}</PktMarkdown>}
        />
    );
}
