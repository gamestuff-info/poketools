import React, {FunctionComponent} from 'react';
import MathJaxComponents from 'mathjax3-react';
import loadComponent from '../loadComponent';
import {Loadable} from 'loadable-components';
import JsxParser from 'react-jsx-parser';

// These are included as loadable components in Markdown text.  Because TypeScript can't check types in dynamically
//  loaded content, these must be cast to Loadable<any> to work.
const FlaggedMoveTable = loadComponent(() => import('../../move/FlaggedMoveTable')) as Loadable<any>;
const MachinePokemonTable = loadComponent(() => import('../../move/MachinePokemonTable')) as Loadable<any>;
const PokemonEvolvesWithItemTable = loadComponent(() => import('../../pokemon/PokemonEvolvesWithItemTable')) as Loadable<any>;
/** Cleanup how Math must be embedded in data */
const MathJax = ((props: { html: string }) => {
    return (
        <MathJaxComponents.Html html={props.html}/>
    );
}) as FunctionComponent<any>;

interface PktMarkdownPropsBase {
    children: string
}

type PktMarkdownProps = PktMarkdownPropsBase & Record<string, any>

export default function PktMarkdown(props: PktMarkdownProps) {
    const className = ['pkt-text'];
    if (props.className) {
        className.concat(props.className.split(' '));
    }

    return (
        <div className={className.join(' ')}>
        <JsxParser jsx={props.children}
                   autoCloseVoidElements
                   renderInWrapper={false}
                   components={{
                       FlaggedMoveTable,
                       MachinePokemonTable,
                       MathJax,
                       PokemonEvolvesWithItemTable,
                   }}
        />
        </div>
    );
}
