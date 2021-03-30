import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';
import PktMarkdown from '../common/components/PktMarkdown';

export default function MoveTeaser(props: { move: ApiRecord.Move.MoveInVersionGroup }) {
    const {move} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    let description = undefined;
    if (move.effect.shortDescription) {
        description = (
            <PktMarkdown>{move.effect.shortDescription.replaceAll('$effect_chance', String(move.effectChance ?? 0))}</PktMarkdown>
        );
    }

    return (
        <Teaser label={move.name}
                href={generatePath(Routes.MOVE_VIEW, {version: currentVersion.slug, move: move.slug})}
                description={description}
        />
    );
}
