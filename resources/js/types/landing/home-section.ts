export interface HeroSectionData {
    image_path?: string | null;
    title?: string | null;
    short_title?: string | null;
    content?: string | null;
}

export interface HeroProps {
    sectionData?: HeroSectionData | null;
}